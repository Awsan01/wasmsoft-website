<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\ServiceController as SiteServiceController;
use App\Http\Requests\Admin\ServiceRequest;
use App\Localization\Locales;
use App\Models\Service;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct(private readonly ContentWriter $writer) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Service::class);

        $status = PublicationStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $services = Service::query()
            ->with('translations')
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('translations', fn (Builder $translation) => Search::apply($translation, ['title', 'slug'], $search)))
            ->ordered()
            ->get();

        return view('admin.services.index', [
            'services' => $services,
            'status' => $status,
            'search' => $search,
            // Reordering a filtered subset would be misleading.
            'canReorder' => $status === null && $search === '' && Gate::allows('reorder', Service::class),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Service::class);

        return view('admin.services.form', ['service' => new Service]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = new Service;

        $this->writer->save($service, $request->serviceAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.services.edit', $service)->with('status', __('admin.content.created'));
    }

    public function edit(Service $service): View
    {
        Gate::authorize('view', $service);

        return view('admin.services.form', ['service' => $service->load(['translations', 'image'])]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->writer->save($service, $request->serviceAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.services.edit', $service)->with('status', __('admin.content.saved'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);

        $this->writer->delete($service);

        return redirect()->route('admin.services.index')->with('status', __('admin.content.deleted'));
    }

    public function move(Request $request, Service $service, ContentCache $cache): RedirectResponse
    {
        Gate::authorize('reorder', Service::class);

        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $service->move($request->input('direction') === 'up' ? -1 : 1);
        $cache->flush();

        return redirect()->route('admin.services.index')->with('status', __('admin.content.moved'));
    }

    public function preview(Service $service, string $locale, Locales $locales, SiteServiceController $site): View
    {
        Gate::authorize('view', $service);

        abort_unless($locales->isSupported($locale), 404);

        $service->load(['translations', 'image']);

        abort_if($service->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $site->render($service, $locale, preview: true);
    }
}
