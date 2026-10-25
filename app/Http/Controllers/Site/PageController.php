<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Http\Controllers\Controller;
use App\Localization\Locales;
use App\Models\Page;
use App\Seo\SeoMeta;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Custom pages at /{locale}/{slug}.
 *
 * Missing translations are handled explicitly: a slug from another
 * language redirects to this language's version when one exists, and
 * otherwise answers 404 with links to the languages the page exists in.
 */
class PageController extends Controller
{
    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    public function show(string $slug): View|Response|RedirectResponse
    {
        $locale = app()->getLocale();

        $page = $this->publishedPageWithSlug($slug, $locale);

        if ($page === null) {
            return $this->notInThisLanguage($slug, $locale);
        }

        $translation = $page->translation($locale);

        return view('site.pages.show', [
            'page' => $page,
            'translation' => $translation,
            'body' => Markdown::toHtml($translation?->body),
            'seo' => new SeoMeta(
                title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
                description: $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body),
                canonical: $page->publicUrl($locale),
                alternates: Alternates::of($page, $this->locales),
            ),
        ]);
    }

    private function notInThisLanguage(string $slug, string $locale): Response|RedirectResponse
    {
        $page = $this->publishedPageWithSlug($slug);

        abort_if($page === null, 404);

        if (($url = $page->publicUrl($locale)) !== null) {
            return redirect()->to($url, 301);
        }

        $alternates = Alternates::of($page, $this->locales);

        abort_if($alternates === [], 404);

        return response()->view('site.translation-missing', [
            'alternates' => $alternates,
            'seo' => new SeoMeta(title: __('site.missing.title').' · '.$this->brand->name(), indexable: false),
        ], 404);
    }

    private function publishedPageWithSlug(string $slug, ?string $locale = null): ?Page
    {
        return Page::query()
            ->custom()
            ->published()
            ->whereHas('translations', function (Builder $translation) use ($slug, $locale): void {
                $translation->where('slug', $slug)->when($locale !== null, fn (Builder $query) => $query->where('locale', $locale));
            })
            ->with('translations')
            ->first();
    }
}
