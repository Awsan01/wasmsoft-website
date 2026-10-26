<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesTranslatedContent;
use App\Localization\Locales;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    use ResolvesTranslatedContent;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    /**
     * Portfolio listing, filterable by category (?category={slug}) and
     * searchable (?q=); filters survive pagination.
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $intro = Page::query()->system(SystemPage::Projects)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.projects.title');

        // Only categories that have published projects in this language.
        $categories = ProjectCategory::query()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->whereHas('projects', fn (Builder $project) => $project->published()->translatedIn($locale))
            ->ordered()
            ->get();

        $categorySlug = $request->string('category')->toString();
        $category = $categorySlug === '' ? null : $categories->first(fn (ProjectCategory $category): bool => $category->translation($locale)?->slug === $categorySlug);

        abort_if($categorySlug !== '' && $category === null, 404);

        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $projects = Project::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->with(['cover', 'category.translations' => fn (HasMany $translation) => $translation->where('locale', $locale)])
            ->when($category, fn (Builder $query, ProjectCategory $category) => $query->whereBelongsTo($category, 'category'))
            ->when($search !== '', fn (Builder $query) => $query->whereHas(
                'translations',
                fn (Builder $translation) => Search::apply($translation->where('locale', $locale), ['title', 'summary'], $search),
            ))
            ->latest('published_at')
            ->orderByDesc('id')
            ->paginate((int) config('wasmsoft.pagination.site'))
            ->withQueryString();

        $canonical = route('site.projects.index', array_filter(['category' => $categorySlug, 'page' => $projects->currentPage() > 1 ? $projects->currentPage() : null]));

        return view('site.projects.index', [
            'title' => $title,
            'lead' => $intro?->summary,
            'categories' => $categories,
            'activeCategory' => $category,
            'search' => $search,
            'projects' => $projects,
            'seo' => new SeoMeta(
                title: ($intro?->meta_title ?: $title).($category ? ' · '.$category->translation($locale)?->name : '').' · '.$this->brand->name(),
                description: $intro?->meta_description ?: Markdown::toText($intro?->summary),
                canonical: $canonical,
                alternates: $category === null ? Alternates::route('site.projects.index', $this->locales) : [],
                // Search results are not worth indexing; categories and pages are.
                indexable: $search === '',
            ),
        ]);
    }

    public function show(string $slug): View|Response|RedirectResponse
    {
        $project = $this->findTranslated(Project::query()->published()->with(['cover', 'gallery', 'category.translations']), $slug);

        if (! $project instanceof Project) {
            return $project;
        }

        return $this->render($project, app()->getLocale());
    }

    /**
     * Render a project in $locale; also used for dashboard previews.
     */
    public function render(Project $project, string $locale, bool $preview = false): View
    {
        $translation = $project->translation($locale);
        $breadcrumbs = [
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.projects'), 'url' => route('site.projects.index')],
            ['label' => (string) $translation?->title, 'url' => null],
        ];

        $related = Project::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->with(['cover', 'category.translations' => fn (HasMany $query) => $query->where('locale', $locale)])
            ->whereKeyNot($project->getKey())
            ->when($project->project_category_id !== null, fn (Builder $query) => $query->where('project_category_id', $project->project_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('site.projects.show', [
            'project' => $project,
            'translation' => $translation,
            'category' => $project->category?->translation($locale),
            'breadcrumbs' => $breadcrumbs,
            'body' => Markdown::toHtml($translation?->body),
            'related' => $related,
            'preview' => $preview,
            'seo' => new SeoMeta(
                title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
                description: $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body),
                canonical: $preview ? null : $project->publicUrl($locale),
                alternates: $preview ? [] : Alternates::of($project, $this->locales),
                image: $project->cover?->urlForWidth(1200),
                imageAlt: $project->cover?->altText($locale),
                indexable: ! $preview,
                structuredData: [StructuredData::breadcrumbs($breadcrumbs, $project->publicUrl($locale))],
            ),
        ]);
    }
}
