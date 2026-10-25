<?php

namespace App\Navigation;

/**
 * A resolved navigation entry, ready for rendering in the current locale.
 */
final readonly class NavLink
{
    /**
     * @param  list<NavLink>  $children
     */
    public function __construct(
        public string $label,
        public ?string $url,
        public bool $active = false,
        public bool $newTab = false,
        public array $children = [],
    ) {}

    public function isExternal(): bool
    {
        return $this->url !== null && ! str_starts_with($this->url, rtrim((string) config('app.url'), '/'));
    }
}
