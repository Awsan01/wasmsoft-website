<?php

namespace App\Navigation;

use App\Content\ContentCache;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Builds the public header and footer menus for a locale from the menus
 * managed in the dashboard. Links with nothing to show in the locale (an
 * untranslated page, a link without a label in that language) and footer
 * groups left without links are skipped. The resolved menus are cached
 * with the other public content; which link is current is decided per
 * request.
 */
class SiteNavigation
{
    public function __construct(private readonly ContentCache $cache) {}

    /**
     * @return list<NavLink>
     */
    public function header(string $locale): array
    {
        $items = $this->cache->remember("navigation.header.{$locale}", fn (): array => $this->resolve(
            MenuItem::query()
                ->with('page.translations')
                ->where('menu', MenuLocation::Header)
                ->whereNull('parent_id')
                ->where('type', '!=', MenuLinkType::Group)
                ->where('is_visible', true)
                ->ordered()
                ->get()
                ->all(),
            $locale,
        ));

        return array_map($this->link(...), $items);
    }

    /**
     * Footer columns: each NavLink is a group heading with children.
     *
     * @return list<NavLink>
     */
    public function footer(string $locale): array
    {
        $groups = $this->cache->remember("navigation.footer.{$locale}", function () use ($locale): array {
            $resolved = [];
            $groups = MenuItem::query()
                ->with(['children' => fn (HasMany $children) => $children->where('is_visible', true), 'children.page.translations'])
                ->where('menu', MenuLocation::Footer)
                ->whereNull('parent_id')
                ->where('type', MenuLinkType::Group)
                ->where('is_visible', true)
                ->ordered()
                ->get();

            foreach ($groups as $group) {
                $heading = $group->resolve($locale);
                $children = $this->resolve($group->children->all(), $locale);

                if ($heading !== null && $children !== []) {
                    $resolved[] = ['label' => $heading['label'], 'children' => $children];
                }
            }

            return $resolved;
        });

        return array_map(
            fn (array $group): NavLink => new NavLink($group['label'], null, children: array_map($this->link(...), $group['children'])),
            $groups,
        );
    }

    /**
     * @param  array<int, MenuItem>  $items
     * @return list<array{label: string, url: ?string, newTab: bool, activeRoute: ?string}>
     */
    private function resolve(array $items, string $locale): array
    {
        return array_values(array_filter(array_map(fn (MenuItem $item): ?array => $item->resolve($locale), $items)));
    }

    /**
     * @param  array{label: string, url: ?string, newTab: bool, activeRoute: ?string}  $item
     */
    private function link(array $item): NavLink
    {
        $active = $item['activeRoute'] !== null
            ? request()->routeIs($item['activeRoute'])
            : $item['url'] !== null && request()->url() === $item['url'];

        return new NavLink($item['label'], $item['url'], $active, $item['newTab']);
    }
}
