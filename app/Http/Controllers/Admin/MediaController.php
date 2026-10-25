<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Media\MediaKind;
use App\Media\MediaLibrary;
use App\Media\MediaUsage;
use App\Models\Media;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Media::class);

        $kind = MediaKind::tryFrom($request->string('kind')->toString());
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $media = Media::query()
            ->when($kind !== null, fn ($query) => $query->where('kind', $kind))
            ->when($search !== '', fn ($query) => Search::apply($query, ['original_name'], $search))
            ->latest()
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'kind' => $kind,
            'search' => $search,
        ]);
    }

    /**
     * Upload from the library page (redirect) or from the image picker (JSON).
     * A batch is all-or-nothing: if one file is rejected, the files already
     * stored from the same batch are removed again.
     */
    public function store(StoreMediaRequest $request, MediaLibrary $library, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $created = [];

        try {
            foreach ($request->uploadedFiles() as $index => $file) {
                $created[] = $library->store($file, $request->user(), "files.{$index}");
            }
        } catch (ValidationException $exception) {
            array_walk($created, fn (Media $media) => $library->delete($media));

            throw $exception;
        }

        foreach ($created as $media) {
            $audit->record(AuditEvent::MediaUploaded, $media, ['kind' => $media->kind->value]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => array_map(fn (Media $media): array => [
                    'id' => $media->id,
                    'url' => $media->urlForWidth(480),
                    'name' => $media->original_name,
                ], $created),
            ], 201);
        }

        return redirect()->route('admin.media.index')->with('status', trans_choice('admin.media.uploaded', count($created), ['count' => count($created)]));
    }

    public function edit(Media $media, MediaUsage $usage): View
    {
        Gate::authorize('view', $media);

        return view('admin.media.edit', [
            'media' => $media->load('uploader'),
            'usageCount' => $usage->count($media),
        ]);
    }

    public function update(UpdateMediaRequest $request, Media $media, AuditLogger $audit): RedirectResponse
    {
        $media->update(['alt' => $request->altTexts()]);
        $audit->record(AuditEvent::MediaUpdated, $media);

        return redirect()->route('admin.media.edit', $media)->with('status', __('admin.media.updated'));
    }

    public function destroy(Media $media, MediaLibrary $library, MediaUsage $usage, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('delete', $media);

        $uses = $usage->count($media);

        if ($uses > 0) {
            return redirect()->route('admin.media.edit', $media)
                ->with('error', trans_choice('admin.media.in_use', $uses, ['count' => $uses]));
        }

        $audit->record(AuditEvent::MediaDeleted, $media);
        $library->delete($media);

        return redirect()->route('admin.media.index')->with('status', __('admin.media.deleted'));
    }

    /**
     * Image grid for the media picker dialog (HTML fragment).
     */
    public function picker(Request $request): View
    {
        Gate::authorize('viewAny', Media::class);

        $search = $request->string('q')->trim()->limit(100, '')->toString();

        return view('admin.media.picker', [
            'images' => Media::query()
                ->images()
                ->when($search !== '', fn ($query) => Search::apply($query, ['original_name'], $search))
                ->latest()
                ->orderByDesc('id')
                ->paginate(18)
                ->withQueryString(),
            'search' => $search,
            'canUpload' => $request->user()?->can('create', Media::class) ?? false,
        ]);
    }
}
