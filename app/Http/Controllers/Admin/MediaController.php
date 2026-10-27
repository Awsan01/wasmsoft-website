<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Localization\Locales;
use App\Media\MediaKind;
use App\Media\MediaLibrary;
use App\Media\MediaUsage;
use App\Models\Media;
use App\Support\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class MediaController extends Controller
{
    use RendersAdminPages;

    /** Images per page of the image picker. */
    private const PICKER_PER_PAGE = 18;

    public function __construct(private readonly AdminPresenter $present) {}

    public function index(Request $request): Response
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

        $kinds = [];

        foreach (MediaKind::cases() as $case) {
            $kinds[$case->value] = $case->label();
        }

        return $this->adminPage('media/index', __('admin.media.title'), [
            'description' => __('admin.media.description'),
            'upload' => Gate::allows('create', Media::class) ? [
                'url' => route('admin.media.store'),
                'hint' => __('admin.media.upload_hint', [
                    'image_mb' => intdiv(MediaKind::Image->maxKilobytes(), 1024),
                    'document_mb' => intdiv(MediaKind::Document->maxKilobytes(), 1024),
                    'count' => StoreMediaRequest::MAX_FILES,
                ]),
                'maxFiles' => StoreMediaRequest::MAX_FILES,
            ] : null,
            'items' => $media->getCollection()->map(fn (Media $item): array => [
                'id' => $item->id,
                'name' => $item->original_name,
                'url' => $item->isImage() ? $item->urlForWidth(480) : null,
                'size' => $this->present->fileSize($item->size),
                'dimensions' => $item->width ? $item->width.'×'.$item->height : null,
                'editUrl' => route('admin.media.edit', $item),
            ])->values()->all(),
            'pagination' => $this->present->pagination($media),
            'filters' => ['q' => $search, 'kind' => $kind->value ?? ''],
            'kindOptions' => $this->present->options($kinds),
            'indexUrl' => route('admin.media.index'),
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
                'data' => array_map(fn (Media $media): ?array => $this->present->media($media), $created),
            ], 201);
        }

        return redirect()->route('admin.media.index')->with('status', trans_choice('admin.media.uploaded', count($created), ['count' => count($created)]));
    }

    public function edit(Media $media, MediaUsage $usage, Locales $locales): Response
    {
        Gate::authorize('view', $media);

        $media->load('uploader');
        $uses = $usage->count($media);
        $alt = [];

        foreach ($locales->supported() as $code) {
            $alt[$code] = (string) $media->altText($code);
        }

        return $this->adminPage('media/edit', $media->original_name, [
            'description' => __('admin.media.edit_title'),
            'file' => [
                'name' => $media->original_name,
                'url' => $media->url(),
                'previewUrl' => $media->isImage() ? $media->urlForWidth(960) : null,
                'width' => $media->width,
                'height' => $media->height,
                'isImage' => $media->isImage(),
            ],
            // `isolate`: names and figures shown in their own direction inside right-to-left text.
            'details' => array_values(array_filter([
                ['label' => __('admin.media.type'), 'value' => $media->kind->label(), 'time' => null, 'isolate' => false],
                ['label' => __('admin.media.size'), 'value' => $this->present->fileSize($media->size), 'time' => null, 'isolate' => true],
                $media->width ? ['label' => __('admin.media.dimensions'), 'value' => $media->width.'×'.$media->height, 'time' => null, 'isolate' => true] : null,
                ['label' => __('admin.media.uploaded_at'), 'value' => null, 'time' => $this->present->time($media->created_at, 'datetime'), 'isolate' => false],
                $media->uploader ? ['label' => __('admin.media.uploaded_by'), 'value' => $media->uploader->name, 'time' => null, 'isolate' => true] : null,
                ['label' => __('admin.media.usage'), 'value' => trans_choice('admin.media.usage_count', $uses, ['count' => $this->present->number($uses)]), 'time' => null, 'isolate' => false],
            ])),
            // Alternative texts describe images; documents have none.
            'alt' => $media->isImage() ? [
                'canEdit' => Gate::allows('update', $media),
                'submit' => ['url' => route('admin.media.update', $media), 'method' => 'put'],
                'values' => $alt,
            ] : null,
            'destroy' => Gate::allows('delete', $media)
                ? $this->present->deletion(route('admin.media.destroy', $media), $media->original_name, __('admin.content.danger_text'))
                : null,
        ], [
            ['label' => __('admin.media.title'), 'url' => route('admin.media.index')],
            ['label' => $media->original_name, 'url' => null],
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
     * Images for the image picker (resources/js/components/admin/media-picker.tsx), newest first.
     */
    public function picker(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);

        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $images = Media::query()
            ->images()
            ->when($search !== '', fn ($query) => Search::apply($query, ['original_name'], $search))
            ->latest()
            ->orderByDesc('id')
            ->paginate(self::PICKER_PER_PAGE)
            ->withQueryString();

        return response()->json([
            'data' => $images->getCollection()->map(fn (Media $image): ?array => $this->present->media($image))->values()->all(),
            'search' => $search,
            'previousUrl' => $images->previousPageUrl(),
            'nextUrl' => $images->nextPageUrl(),
        ]);
    }
}
