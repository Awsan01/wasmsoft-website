<?php

namespace App\Navigation;

/**
 * A resolved navigation entry, ready for rendering in the current locale.
 * `panel` names the menu panel a header link opens (its fixed page's
 * content, e.g. the list of services), or null for a plain link.
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
        public ?string $panel = null,
    ) {}

    public function isExternal(): bool
    {
        return $this->url !== null && ! str_starts_with($this->url, rtrim((string) config('app.url'), '/'));
    }

    /**
     * @return array{label: string, url: string|null, current: bool, newTab: bool, external: bool, panel: string|null, children: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'url' => $this->url,
            'current' => $this->active,
            'newTab' => $this->newTab,
            'external' => $this->isExternal(),
            'panel' => $this->panel,
            'children' => array_map(fn (NavLink $child): array => $child->toArray(), $this->children),
        ];
    }
}
