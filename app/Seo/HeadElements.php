<?php

namespace App\Seo;

use App\Localization\Locales;
use App\Support\Brand;

/**
 * The <head> elements of a public page (title, description, canonical and
 * hreflang links, Open Graph, JSON-LD) as HTML strings.
 *
 * Laravel prints them into the first HTML response, so search engines and
 * link previews never depend on JavaScript; the same list travels as the
 * page's `head` prop and Inertia swaps the elements on every client-side
 * visit. Each element carries a stable `data-inertia` key so the browser
 * replaces it instead of adding a second copy.
 */
class HeadElements
{
    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    /**
     * @return list<string>
     */
    public function for(SeoMeta $seo, string $locale): array
    {
        // Pages without their own image are shared with the site-wide image.
        $image = $seo->image ?? $this->brand->socialImageUrl();

        $elements = [$this->element('title', 'title', [], $seo->title)];

        if ($seo->description) {
            $elements[] = $this->element('description', 'meta', ['name' => 'description', 'content' => $seo->description]);
        }

        if (! $seo->indexable) {
            $elements[] = $this->element('robots', 'meta', ['name' => 'robots', 'content' => 'noindex, nofollow']);
        }

        if ($seo->canonical) {
            $elements[] = $this->element('canonical', 'link', ['rel' => 'canonical', 'href' => $seo->canonical]);
        }

        if (count($seo->alternates) > 1) {
            foreach ($seo->alternates as $code => $url) {
                $elements[] = $this->element("alternate-{$code}", 'link', ['rel' => 'alternate', 'hreflang' => $code, 'href' => $url]);
            }

            if (isset($seo->alternates[$default = $this->locales->default()])) {
                $elements[] = $this->element('alternate-x-default', 'link', ['rel' => 'alternate', 'hreflang' => 'x-default', 'href' => $seo->alternates[$default]]);
            }
        }

        $elements[] = $this->property('og:site_name', $this->brand->name($locale));
        $elements[] = $this->property('og:type', $seo->type);
        $elements[] = $this->property('og:title', $seo->title);

        if ($seo->description) {
            $elements[] = $this->property('og:description', $seo->description);
        }

        if ($seo->canonical) {
            $elements[] = $this->property('og:url', $seo->canonical);
        }

        $elements[] = $this->property('og:locale', $this->locales->openGraph($locale));

        foreach (array_keys($seo->alternates) as $code) {
            if ($code !== $locale) {
                $elements[] = $this->element("og:locale:alternate-{$code}", 'meta', ['property' => 'og:locale:alternate', 'content' => $this->locales->openGraph($code)]);
            }
        }

        $elements[] = $this->property('og:image', $image);

        if ($seo->image && $seo->imageAlt) {
            $elements[] = $this->property('og:image:alt', $seo->imageAlt);
        }

        $elements[] = $this->element('twitter:card', 'meta', ['name' => 'twitter:card', 'content' => $seo->image ? 'summary_large_image' : 'summary']);

        if ($seo->type === 'article' && $seo->publishedAt) {
            $elements[] = $this->property('article:published_time', $seo->publishedAt->toAtomString());
        }

        if ($seo->type === 'article' && $seo->modifiedAt) {
            $elements[] = $this->property('article:modified_time', $seo->modifiedAt->toAtomString());
        }

        // JSON-LD is a data block, not script: the CSP does not apply and no nonce is needed.
        foreach ($seo->structuredData as $index => $data) {
            $elements[] = '<script data-inertia="json-ld-'.$index.'" type="application/ld+json">'.json_encode($data, self::JSON_FLAGS).'</script>';
        }

        return $elements;
    }

    private function property(string $property, string $content): string
    {
        return $this->element($property, 'meta', ['property' => $property, 'content' => $content]);
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function element(string $key, string $tag, array $attributes, ?string $text = null): string
    {
        $html = '<'.$tag.' data-inertia="'.e($key).'"';

        foreach ($attributes as $name => $value) {
            $html .= ' '.$name.'="'.e($value).'"';
        }

        return $text === null ? $html.'>' : $html.'>'.e($text).'</'.$tag.'>';
    }
}
