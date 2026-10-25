<?php

namespace App\Content;

use App\Localization\Locales;

/**
 * The language versions of a content item: locale => public URL, for the
 * enabled locales in which it is published. Feeds hreflang tags and the
 * language switcher, so visitors are only ever sent to real translations.
 */
final class Alternates
{
    /**
     * @return array<string, string>
     */
    public static function of(PubliclyRoutable $item, Locales $locales): array
    {
        $urls = [];

        foreach ($locales->enabled() as $locale) {
            if (($url = $item->publicUrl($locale)) !== null) {
                $urls[$locale] = $url;
            }
        }

        return $urls;
    }

    /**
     * A fixed route (home, listings) exists in every enabled locale.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<string, string>
     */
    public static function route(string $name, Locales $locales, array $parameters = []): array
    {
        $urls = [];

        foreach ($locales->enabled() as $locale) {
            $urls[$locale] = route($name, ['locale' => $locale, ...$parameters]);
        }

        return $urls;
    }
}
