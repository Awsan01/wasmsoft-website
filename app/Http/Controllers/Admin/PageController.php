<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Content\SystemPages;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PageController as SitePageController;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Responses\SitePreview;
use App\Localization\Locales;
use App\Models\Page;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PageController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        private readonly ContentWriter $writer,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request, SystemPages $systemPages): Response
    {
        Gate::authorize('viewAny', Page::class);

        $systemPages->ensure();

        $status = PublicationStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $pages = Page::query()
            ->custom()
            ->with('translations')
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('translations', fn (Builder $translation) => Search::apply($translation, ['title', 'slug'], $search)))
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate((int) config('wasmsoft.pagination.admin'))
            ->withQueryString();

        $order = array_map(fn (SystemPage $page): string => $page->value, SystemPage::cases());

        $system = Page::query()
            ->whereNotNull('system_key')
            ->with('translations')
            ->get()
            ->sortBy(fn (Page $page): int|false => array_search($page->system_key?->value, $order, true))
            ->values();

        return $this->adminPage('pages/index', __('admin.pages.title'), [
            'description' => __('admin.pages.description'),
            'createUrl' => Gate::allows('create', Page::class) ? route('admin.pages.create') : null,
            'systemPages' => $system->map(fn (Page $page): array => [
                'id' => $page->id,
                'label' => (string) $page->system_key?->label(),
                'locales' => $this->present->locales($page),
                'updated' => $this->present->time($page->updated_at),
                'editUrl' => route('admin.pages.edit', $page),
            ])->all(),
            'pages' => $pages->getCollection()->map(fn (Page $page): array => [
                'id' => $page->id,
                'title' => $this->present->display($page, 'title', __('admin.pages.untitled')),
                'template' => $page->template->label(),
                'status' => $this->present->status($page),
                'locales' => $this->present->locales($page),
                'updated' => $this->present->time($page->updated_at),
                'editUrl' => route('admin.pages.edit', $page),
            ])->values()->all(),
            'pagination' => $this->present->pagination($pages),
            'filters' => ['q' => $search, 'status' => $status->value ?? ''],
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'indexUrl' => route('admin.pages.index'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Page::class);

        return $this->form(new Page);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $page = new Page;

        $this->writer->save($page, $request->pageAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.content.created'));
    }

    public function edit(Page $page): Response
    {
        Gate::authorize('view', $page);

        return $this->form($page->load('translations'));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $this->writer->save($page, $request->pageAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.content.saved'));
    }

    public function destroy(Page $page): RedirectResponse
    {
        Gate::authorize('delete', $page);

        $this->writer->delete($page);

        return redirect()->route('admin.pages.index')->with('status', __('admin.content.deleted'));
    }

    /**
     * The page as visitors would see it in $locale, drafts included.
     */
    public function preview(Request $request, Page $page, string $locale, Locales $locales, SitePageController $site, SitePreview $sitePreview): HttpResponse
    {
        Gate::authorize('view', $page);

        abort_unless(! $page->isSystem() && $locales->isSupported($locale), 404);

        $page->load('translations');

        abort_if($page->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $sitePreview->render($request, fn () => $site->render($page, $locale, preview: true));
    }

    /**
     * The form of a page. System pages have a fixed address and are always
     * published: no slug, template or status.
     */
    private function form(Page $page): Response
    {
        $isNew = ! $page->exists;
        $system = $page->isSystem();
        $title = match (true) {
            $isNew => __('admin.pages.new'),
            $system => (string) $page->system_key?->label(),
            default => $page->displayTranslation()->title ?? __('admin.pages.untitled'),
        };

        $editor = $this->present->editor($page, [
            'title' => ['required' => true, 'maxlength' => 200],
            ...($system ? [] : ['slug' => ['type' => 'slug', 'maxlength' => 120]]),
            'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.pages.summary_hint')],
            'body' => ['type' => 'markdown', 'rows' => 14],
            'meta_title' => ['seo' => true, 'maxlength' => 70],
            'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
        ]);

        return $this->adminPage('pages/form', $title, [
            'description' => $system ? __('admin.pages.system_edit_description') : null,
            'canEdit' => $isNew ? Gate::allows('create', Page::class) : Gate::allows('update', $page),
            'submit' => $this->present->formAction($page, 'admin.pages'),
            'editor' => $editor,
            'form' => [
                'translations' => $this->present->translationValues($page, $editor['fields']),
                ...($system ? [] : ['status' => $page->status->value, 'template' => $page->template->value]),
            ],
            // System pages show their (fixed) status instead of a choice.
            'fixedStatus' => $system ? $this->present->status($page) : null,
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'templateOptions' => $this->present->options(PageTemplate::options()),
            'viewLinks' => $this->present->viewLinks($page, 'admin.pages.preview'),
            'destroy' => ! $isNew && Gate::allows('delete', $page)
                ? $this->present->deletion(route('admin.pages.destroy', $page), $title, __('admin.content.danger_text'))
                : null,
        ], [
            ['label' => __('admin.pages.title'), 'url' => route('admin.pages.index')],
            ['label' => $title, 'url' => null],
        ]);
    }
}
