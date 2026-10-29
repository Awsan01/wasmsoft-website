<?php

namespace App\Content;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

/**
 * Renders editor-written Markdown (GitHub flavour: tables, lists, links).
 *
 * Raw HTML in the source is escaped and javascript:/data: links are
 * dropped, so content entered in the dashboard cannot inject markup or
 * scripts into the public site.
 */
final class Markdown
{
    private const OPTIONS = [
        'html_input' => 'escape',
        'allow_unsafe_links' => false,
        'max_nesting_level' => 20,
    ];

    public static function toHtml(?string $markdown): HtmlString
    {
        if ($markdown === null || trim($markdown) === '') {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown($markdown, self::OPTIONS));
    }

    /**
     * The Markdown cut at its main headings (# and ##), rendered like
     * toHtml(): each heading as plain text and the HTML of what follows it,
     * up to the next one. What comes before the first heading is a section
     * without a heading.
     *
     * @return list<array{heading: string|null, html: string}>
     */
    public static function sections(?string $markdown): array
    {
        if ($markdown === null || trim($markdown) === '') {
            return [];
        }

        $environment = (new GithubFlavoredMarkdownConverter(self::OPTIONS))->getEnvironment();
        $document = (new MarkdownParser($environment))->parse($markdown);
        $renderer = new HtmlRenderer($environment);

        $sections = [];
        $heading = null;
        $nodes = [];

        foreach ($document->children() as $node) {
            if (! $node instanceof Heading || $node->getLevel() > 2) {
                $nodes[] = $node;

                continue;
            }

            // Before the first heading there is a section only if there is text.
            if ($heading !== null || $nodes !== []) {
                $sections[] = self::section($renderer, $heading, $nodes);
            }

            $heading = $node;
            $nodes = [];
        }

        if ($heading !== null || $nodes !== []) {
            $sections[] = self::section($renderer, $heading, $nodes);
        }

        return $sections;
    }

    /**
     * @param  list<Node>  $nodes
     * @return array{heading: string|null, html: string}
     */
    private static function section(HtmlRenderer $renderer, ?Heading $heading, array $nodes): array
    {
        $text = $heading === null ? '' : self::plainText($renderer->renderNodes($heading->children()));

        return ['heading' => $text === '' ? null : $text, 'html' => $renderer->renderNodes($nodes)];
    }

    /**
     * Plain text of the Markdown, e.g. for meta descriptions and excerpts.
     */
    public static function toText(?string $markdown, int $limit = 160): string
    {
        return Str::limit(self::plainText((string) self::toHtml($markdown)), $limit);
    }

    private static function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
