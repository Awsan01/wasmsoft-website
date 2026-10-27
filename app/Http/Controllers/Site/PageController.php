<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\HighlightGroup;
use App\Content\Markdown;
use App\Content\PageTemplate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesTranslatedContent;
use App\Http\Presenters\SitePresenter;
use App\Localization\Locales;
use App\Models\Highlight;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\TeamMember;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Custom pages at /{locale}/{slug}. The page's template adds sections of
 * its own: the about page shows the company's values, team and history,
 * the partnerships page the ways to partner.
 */
class PageController extends Controller
{
    use ResolvesTranslatedContent;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SitePresenter $presenter,
    ) {}

    public function show(string $slug): Response|SymfonyResponse|RedirectResponse
    {
        $page = $this->findTranslated(Page::query()->custom()->published(), $slug);

        if (! $page instanceof Page) {
            return $page;
        }

        return $this->render($page, app()->getLocale());
    }

    /**
     * Render a page in $locale. Also used for dashboard previews of drafts,
     * which are never indexable and offer no language alternates.
     */
    public function render(Page $page, string $locale, bool $preview = false): Response
    {
        $translation = $page->translation($locale);
        $breadcrumbs = [
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => (string) $translation?->title, 'url' => null],
        ];

        $props = [
            'page' => [
                'title' => (string) $translation?->title,
                'summary' => $translation?->summary,
                'body' => Markdown::toHtml($translation?->body)->toHtml(),
            ],
            'breadcrumbs' => $breadcrumbs,
        ];

        $component = match ($page->template) {
            PageTemplate::About => 'site/pages/about',
            PageTemplate::Partnerships => 'site/pages/partnerships',
            PageTemplate::Default => 'site/pages/show',
        };

        if ($page->template === PageTemplate::About) {
            $props['values'] = $this->highlights(HighlightGroup::Value, $locale);
            $props['team'] = TeamMember::query()->shownIn($locale)->with('photo')->get()
                ->map(fn (TeamMember $member): array => $this->presenter->teamMember($member, $locale))->values()->all();
            $props['milestones'] = Milestone::query()->shownIn($locale)->get()
                ->map(fn (Milestone $milestone): array => $this->presenter->milestone($milestone, $locale))->values()->all();
        }

        if ($page->template === PageTemplate::Partnerships) {
            $props['models'] = $this->highlights(HighlightGroup::Partnership, $locale);
        }

        return $this->sitePage($component, $props, new SeoMeta(
            title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
            description: $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body),
            canonical: $preview ? null : $page->publicUrl($locale),
            alternates: $preview ? [] : Alternates::of($page, $this->locales),
            indexable: ! $preview,
            structuredData: [StructuredData::breadcrumbs($breadcrumbs, $page->publicUrl($locale))],
        ), preview: $preview);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function highlights(HighlightGroup $group, string $locale): array
    {
        return array_values(Highlight::query()->shownIn($group, $locale)->get()
            ->map(fn (Highlight $highlight): array => $this->presenter->highlight($highlight, $locale))->all());
    }
}
