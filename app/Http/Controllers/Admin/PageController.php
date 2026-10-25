<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Content\SystemPages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PageController as SitePageController;
use App\Http\Requests\Admin\PageRequest;
use App\Localization\Locales;
use App\Models\Page;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class PageController extends Controller
{
    public function __construct(private readonly ContentWriter $writer) {}

    public function index(Request $request, SystemPages $systemPages): View
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

        $systemPages = Page::query()
            ->whereNotNull('system_key')
            ->with('translations')
            ->get()
            ->sortBy(fn (Page $page): int|false => array_search($page->system_key?->value, $order, true))
            ->values();

        return view('admin.pages.index', [
            'pages' => $pages,
            'systemPages' => $systemPages,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Page::class);

        return view('admin.pages.form', ['page' => new Page]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $page = new Page;

        $this->writer->save($page, $request->pageAttributes(), $request->status(), $request->translations());

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.content.created'));
    }

    public function edit(Page $page): View
    {
        Gate::authorize('view', $page);

        return view('admin.pages.form', ['page' => $page->load('translations')]);
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
    public function preview(Page $page, string $locale, Locales $locales, SitePageController $site): View
    {
        Gate::authorize('view', $page);

        abort_unless(! $page->isSystem() && $locales->isSupported($locale), 404);

        $page->load('translations');

        abort_if($page->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $site->render($page, $locale, preview: true);
    }
}
