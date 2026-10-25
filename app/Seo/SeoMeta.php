<?php

namespace App\Seo;

use Carbon\CarbonInterface;

/**
 * Search and social metadata for one public page.
 *
 * `alternates` maps locale => absolute URL and must only contain locales in
 * which this page actually exists; it drives both the hreflang tags and the
 * language switcher, so visitors are never sent to a missing translation.
 */
final readonly class SeoMeta
{
    /**
     * @param  array<string, string>  $alternates
     * @param  list<array<string, mixed>>  $structuredData  JSON-LD objects
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $canonical = null,
        public array $alternates = [],
        public ?string $image = null,
        public ?string $imageAlt = null,
        public string $type = 'website',
        public bool $indexable = true,
        public array $structuredData = [],
        public ?CarbonInterface $publishedAt = null,
        public ?CarbonInterface $modifiedAt = null,
    ) {}

    public function with(mixed ...$changes): self
    {
        return new self(...array_merge(get_object_vars($this), $changes));
    }
}
