<?php

namespace App\Support;

/**
 * Cleans up the @font-face rules written by the Vite font pipeline.
 *
 * laravel-vite-plugin (3.2) writes one rule per font file, so every WOFF2
 * subset gets a twin rule for its WOFF file with the same family, weight,
 * style and unicode-range. Browsers treat the later twin as the better
 * match and download the larger WOFF file, wasting the preloaded WOFF2
 * one. Every browser the site supports reads WOFF2, so those twins are
 * dropped; a WOFF rule without a WOFF2 twin is kept.
 */
class FontFaceRules
{
    public static function withoutWoffTwins(string $css): string
    {
        $rules = preg_match_all('/@font-face\s*\{[^}]*\}/', $css, $matches) > 0 ? $matches[0] : [];
        $woff2 = [];

        foreach ($rules as $rule) {
            if (str_contains($rule, 'format("woff2")')) {
                $woff2[self::descriptors($rule)] = true;
            }
        }

        foreach ($rules as $rule) {
            $isWoffOnly = str_contains($rule, 'format("woff")') && ! str_contains($rule, 'format("woff2")');

            if ($isWoffOnly && isset($woff2[self::descriptors($rule)])) {
                $css = str_replace($rule, '', $css);
            }
        }

        return (string) preg_replace("/\n{3,}/", "\n\n", $css);
    }

    /**
     * The rule without its src: what makes two rules describe the same face.
     */
    private static function descriptors(string $rule): string
    {
        return (string) preg_replace('/\s*src:[^;]*;/', '', $rule);
    }
}
