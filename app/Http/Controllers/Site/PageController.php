<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\HighlightGroup;
use App\Content\Markdown;
use App\Content\PageTemplate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesTranslatedContent;
use App\Localization\Locales;
use App\Models\Highlight;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\TeamMember;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Custom pages at /{locale}/{slug}.
 */
class PageController extends Controller
{
    use ResolvesTranslatedContent;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    public function show(string $slug): View|Response|RedirectResponse
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
    public function render(Page $page, string $locale, bool $preview = false): View
    {
        $translation = $page->translation($locale);
        $breadcrumbs = [
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => (string) $translation?->title, 'url' => null],
        ];

        $about = $page->template === PageTemplate::About;

        return view($about ? 'site.pages.about' : 'site.pages.show', [
            'page' => $page,
            'translation' => $translation,
            'breadcrumbs' => $breadcrumbs,
            'body' => Markdown::toHtml($translation?->body),
            'preview' => $preview,
            // The about template adds the company's values, team and history.
            'values' => $about ? Highlight::query()->shownIn(HighlightGroup::Value, $locale)->get() : null,
            'team' => $about ? TeamMember::query()->shownIn($locale)->with('photo')->get() : null,
            'milestones' => $about ? Milestone::query()->shownIn($locale)->get() : null,
            'seo' => new SeoMeta(
                title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
                description: $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body),
                canonical: $preview ? null : $page->publicUrl($locale),
                alternates: $preview ? [] : Alternates::of($page, $this->locales),
                indexable: ! $preview,
                structuredData: [StructuredData::breadcrumbs($breadcrumbs, $page->publicUrl($locale))],
            ),
        ]);
    }
}
