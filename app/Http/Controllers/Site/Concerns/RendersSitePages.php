<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Localization\Locales;
use App\Seo\HeadElements;
use App\Seo\SeoMeta;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders a page of the public site (an Inertia page under
 * resources/js/pages/site) with what every page carries besides its own
 * data: the <head> elements for search engines and link previews, the
 * language switcher's destinations and the preview flag.
 */
trait RendersSitePages
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, string>  $languageUrls  locale => where the language switcher points for
     *                                               languages this page is not translated into
     *                                               (defaults to that language's home page)
     */
    protected function sitePage(string $component, array $props, SeoMeta $seo, array $languageUrls = [], bool $preview = false): Response
    {
        $locale = app()->getLocale();

        return Inertia::render($component, [
            ...$props,
            'head' => app(HeadElements::class)->for($seo, $locale),
            'languages' => $this->languageLinks($seo->alternates, $languageUrls, $locale),
            'preview' => $preview,
        ]);
    }

    /**
     * Every enabled language, pointing to this page's translation when it
     * exists (alternates only list real translations).
     *
     * @param  array<string, string>  $alternates
     * @param  array<string, string>  $languageUrls
     * @return list<array{code: string, name: string, dir: string, url: string, current: bool}>
     */
    private function languageLinks(array $alternates, array $languageUrls, string $current): array
    {
        $locales = app(Locales::class);

        return array_map(fn (string $code): array => [
            'code' => $code,
            'name' => $locales->nativeName($code),
            'dir' => $locales->direction($code),
            'url' => $alternates[$code] ?? $languageUrls[$code] ?? route('site.home', ['locale' => $code]),
            'current' => $code === $current,
        ], $locales->enabled());
    }
}
