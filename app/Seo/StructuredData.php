<?php

namespace App\Seo;

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
}
