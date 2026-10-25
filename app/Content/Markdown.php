<?php

namespace App\Content;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Renders editor-written Markdown (GitHub flavour: tables, lists, links).
 *
 * Raw HTML in the source is escaped and javascript:/data: links are
 * dropped, so content entered in the dashboard cannot inject markup or
 * scripts into the public site.
 */
final class Markdown
{
    public static function toHtml(?string $markdown): HtmlString
    {
        if ($markdown === null || trim($markdown) === '') {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]));
    }

    /**
     * Plain text of the Markdown, e.g. for meta descriptions and excerpts.
     */
    public static function toText(?string $markdown, int $limit = 160): string
    {
        $text = strip_tags((string) self::toHtml($markdown));
        $text = (string) preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5));

        return Str::limit(trim($text), $limit);
    }
}
