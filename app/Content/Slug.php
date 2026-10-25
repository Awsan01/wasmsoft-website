<?php

namespace App\Content;

use Closure;
use Illuminate\Support\Str;

/**
 * URL slugs for translated content.
 *
 * Arabic slugs keep Arabic script (better for Arabic readers and search),
 * without diacritics or tatweel and with Western digits. English and
 * Turkish slugs are transliterated to ASCII ("Yazılım Geliştirme" becomes
 * "yazilim-gelistirme").
 */
final class Slug
{
    /** Letters and numbers in any script, separated by single hyphens. */
    public const PATTERN = '/^[\p{L}\p{N}]+(?:-[\p{L}\p{N}]+)*$/u';

    public const MAX_LENGTH = 120;

    private const ARABIC_MARKS = '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u';

    private const ARABIC_DIGITS = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    public static function make(string $text, string $locale): string
    {
        $slug = $locale === 'ar' ? self::unicode($text) : Str::slug($text, '-', $locale);

        return self::trimToLength($slug);
    }

    public static function isValid(string $slug): bool
    {
        return mb_strlen($slug) <= self::MAX_LENGTH && preg_match(self::PATTERN, $slug) === 1;
    }

    /**
     * First free variant of $slug: "web", "web-2", "web-3"…
     *
     * @param  Closure(string): bool  $taken
     */
    public static function unique(string $slug, Closure $taken): string
    {
        $candidate = $slug;

        for ($suffix = 2; $taken($candidate); $suffix++) {
            $ending = "-{$suffix}";
            $candidate = self::trimToLength($slug, self::MAX_LENGTH - mb_strlen($ending)).$ending;
        }

        return $candidate;
    }

    private static function unicode(string $text): string
    {
        $text = (string) preg_replace(self::ARABIC_MARKS, '', $text);
        $text = mb_strtolower(strtr($text, self::ARABIC_DIGITS));

        return trim((string) preg_replace('/[^\p{L}\p{N}]+/u', '-', $text), '-');
    }

    private static function trimToLength(string $slug, int $length = self::MAX_LENGTH): string
    {
        return rtrim(mb_substr($slug, 0, $length), '-');
    }
}
