<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\ServiceController as SiteServiceController;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\ServiceRequest;
use App\Http\Responses\SitePreview;
use App\Localization\Locales;
use App\Models\Service;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ServiceController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        private readonly ContentWriter $writer,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request): Response
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

        return $this->adminPage('services/index', __('admin.services.title'), [
            'description' => __('admin.services.description'),
            'createUrl' => Gate::allows('create', Service::class) ? route('admin.services.create') : null,
            'services' => $services->map(fn (Service $service): array => [
                'id' => $service->id,
                'title' => $this->present->display($service, 'title', __('admin.services.untitled')),
                'icon' => $service->icon?->value,
                'featured' => $service->is_featured,
                'status' => $this->present->status($service),
                'locales' => $this->present->locales($service),
                'updated' => $this->present->time($service->updated_at),
                'editUrl' => route('admin.services.edit', $service),
                'moveUrl' => route('admin.services.move', $service),
            ])->all(),
            // Reordering a filtered subset would be misleading.
            'canReorder' => $status === null && $search === '' && Gate::allows('reorder', Service::class),
            'filters' => ['q' => $search, 'status' => $status->value ?? ''],
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'indexUrl' => route('admin.services.index'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Service::class);

        return $this->form(new Service);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = new Service;

        $this->writer->save($service, $request->serviceAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.services.edit', $service)->with('status', __('admin.content.created'));
    }

    public function edit(Service $service): Response
    {
        Gate::authorize('view', $service);

        return $this->form($service->load(['translations', 'image']));
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

    public function preview(Request $request, Service $service, string $locale, Locales $locales, SiteServiceController $site, SitePreview $sitePreview): HttpResponse
    {
        Gate::authorize('view', $service);

        abort_unless($locales->isSupported($locale), 404);

        $service->load(['translations', 'image']);

        abort_if($service->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $sitePreview->render($request, fn () => $site->render($service, $locale, preview: true));
    }

    private function form(Service $service): Response
    {
        $isNew = ! $service->exists;
        $title = $isNew ? __('admin.services.new') : ($service->displayTranslation()->title ?? __('admin.services.untitled'));

        $editor = $this->present->editor($service, [
            'title' => ['required' => true, 'maxlength' => 200],
            'slug' => ['type' => 'slug', 'maxlength' => 120],
            'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.services.summary_hint')],
            'body' => ['type' => 'markdown', 'rows' => 12],
            'benefits' => ['type' => 'lines', 'rows' => 5, 'hint' => __('admin.services.benefits_hint')],
            'meta_title' => ['seo' => true, 'maxlength' => 70],
            'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
        ]);

        return $this->adminPage('services/form', $title, [
            'canEdit' => $isNew ? Gate::allows('create', Service::class) : Gate::allows('update', $service),
            'submit' => $this->present->formAction($service, 'admin.services'),
            'editor' => $editor,
            'form' => [
                'translations' => $this->present->translationValues($service, $editor['fields']),
                'status' => $service->status->value,
                'is_featured' => $service->is_featured,
                'image' => $this->present->media($service->image),
                'icon' => $service->icon->value ?? '',
            ],
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'icons' => $this->present->icons(),
            'viewLinks' => $this->present->viewLinks($service, 'admin.services.preview'),
            'destroy' => ! $isNew && Gate::allows('delete', $service)
                ? $this->present->deletion(route('admin.services.destroy', $service), $title, __('admin.content.danger_text'))
                : null,
        ], [
            ['label' => __('admin.services.title'), 'url' => route('admin.services.index')],
            ['label' => $title, 'url' => null],
        ]);
    }
}
