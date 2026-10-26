<?php

namespace App\Seo;

use Carbon\CarbonInterface;

/**
 * schema.org objects for JSON-LD, built only from content that is actually
 * shown on the page.
 */
final class StructuredData
{
    /**
     * BreadcrumbList from the page's visible breadcrumbs; the last item
     * (the current page) links to $currentUrl.
     *
     * @param  list<array{label: string, url: ?string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items, ?string $currentUrl): array
    {
        $elements = [];

        foreach ($items as $index => $item) {
            $url = $item['url'] ?? $currentUrl;

            $elements[] = array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $url,
            ], fn (mixed $value): bool => $value !== null);
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * Article (news post). Without a named author, the company is the author.
     *
     * @return array<string, mixed>
     */
    public static function article(
        string $headline,
        ?string $description,
        ?string $url,
        ?string $image,
        ?CarbonInterface $publishedAt,
        ?CarbonInterface $modifiedAt,
        ?string $author,
        string $publisher,
        string $locale,
    ): array {
        $organization = ['@type' => 'Organization', 'name' => $publisher];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'url' => $url,
            'mainEntityOfPage' => $url,
            'image' => $image === null ? null : [$image],
            'datePublished' => $publishedAt?->toAtomString(),
            'dateModified' => $modifiedAt?->toAtomString(),
            'inLanguage' => $locale,
            'author' => $author === null ? $organization : ['@type' => 'Person', 'name' => $author],
            'publisher' => $organization,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
