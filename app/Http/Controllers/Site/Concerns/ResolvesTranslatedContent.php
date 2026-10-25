<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Content\Alternates;
use App\Content\PubliclyRoutable;
use App\Localization\Locales;
use App\Seo\SeoMeta;
use App\Support\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Finds published content by its slug in the current language, applying
 * the missing-translation policy:
 *
 *  - a slug of another language redirects (301) to this language's version;
 *  - content that exists only in other languages answers 404 with links to them;
 *  - anything else is a plain 404.
 */
trait ResolvesTranslatedContent
{
    /**
     * @template TContent of Model
     *
     * @param  Builder<TContent>  $published  query of the published content of one type
     * @return TContent|Response|RedirectResponse
     */
    protected function findTranslated(Builder $published, string $slug): Model|Response|RedirectResponse
    {
        $locale = app()->getLocale();

        $content = $this->withSlug($published, $slug, $locale);

        if ($content !== null) {
            return $content;
        }

        $elsewhere = $this->withSlug($published, $slug, null);

        abort_unless($elsewhere instanceof PubliclyRoutable, 404);

        if (($url = $elsewhere->publicUrl($locale)) !== null) {
            return redirect()->to($url, 301);
        }

        $alternates = Alternates::of($elsewhere, app(Locales::class));

        abort_if($alternates === [], 404);

        return response()->view('site.translation-missing', [
            'alternates' => $alternates,
            'seo' => new SeoMeta(title: __('site.missing.title').' · '.app(Brand::class)->name(), indexable: false),
        ], 404);
    }

    /**
     * @template TContent of Model
     *
     * @param  Builder<TContent>  $published
     * @return TContent|null
     */
    private function withSlug(Builder $published, string $slug, ?string $locale): ?Model
    {
        return (clone $published)
            ->whereHas('translations', function (Builder $translation) use ($slug, $locale): void {
                $translation->where('slug', $slug);

                if ($locale !== null) {
                    $translation->where('locale', $locale);
                }
            })
            ->with('translations')
            ->first();
    }
}
