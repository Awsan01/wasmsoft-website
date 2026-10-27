<?php

namespace App\Navigation;

use App\Content\ContentCache;
use App\Content\SystemPage;
use App\Http\Presenters\SitePresenter;
use App\Models\MenuItem;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Builds the public header and footer menus for a locale from the menus
 * managed in the dashboard. Links with nothing to show in the locale (an
 * untranslated page, a link without a label in that language) and footer
 * groups left without links are skipped. The resolved menus are cached
 * with the other public content; which link is current is decided per
 * request.
 *
 * Header links to the services and projects pages open a panel with that
 * page's published content (see panels()).
 *
 * @phpstan-import-type ServiceCard from SitePresenter
 * @phpstan-import-type ProjectCard from SitePresenter
 *
 * @phpstan-type ResolvedItem array{label: string, url: ?string, newTab: bool, activeRoute: ?string, system?: ?string}
 * @phpstan-type Panels array{services?: list<ServiceCard>, projects?: array{featured: list<ProjectCard>, categories: list<array{name: string, url: string}>}}
 */
class SiteNavigation
{
    /** Fixed pages whose header link opens a panel, and the panel's name. */
    private const PANELS = [
        SystemPage::Services->value => 'services',
        SystemPage::Projects->value => 'projects',
    ];

    public function __construct(
        private readonly ContentCache $cache,
        private readonly SitePresenter $presenter,
    ) {}

    /**
     * @return list<NavLink>
     */
    public function header(string $locale): array
    {
        $items = $this->cache->remember("navigation.header.v2.{$locale}", fn (): array => $this->resolve(
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
        $groups = $this->cache->remember("navigation.footer.v2.{$locale}", function () use ($locale): array {
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
     * Content of the panels opened by the given header links: the published
     * services, and the featured projects with the project categories.
     * Panels without content are left out (their link stays a plain link).
     *
     * @param  list<NavLink>  $links
     * @return Panels
     */
    public function panels(array $links, string $locale): array
    {
        $wanted = array_values(array_unique(array_filter(array_map(fn (NavLink $link): ?string => $link->panel, $links))));

        if ($wanted === []) {
            return [];
        }

        /** @var Panels $panels */
        $panels = $this->cache->remember('navigation.panels.v1.'.$locale.'.'.implode(',', $wanted), function () use ($wanted, $locale): array {
            $panels = [];

            if (in_array('services', $wanted, true)) {
                $services = Service::query()->published()->translatedIn($locale)->withTranslation($locale)->ordered()->limit(8)->get();

                if ($services->isNotEmpty()) {
                    $panels['services'] = array_values($services->map(fn (Service $service): array => $this->presenter->service($service, $locale))->all());
                }
            }

            if (in_array('projects', $wanted, true)) {
                $categoryIn = fn (HasMany $translation) => $translation->where('locale', $locale);
                $featured = Project::query()->published()->translatedIn($locale)->withTranslation($locale)
                    ->with(['cover', 'category.translations' => $categoryIn])
                    ->orderByDesc('is_featured')->latest('published_at')->orderByDesc('id')->limit(2)->get();

                if ($featured->isNotEmpty()) {
                    $categories = ProjectCategory::query()
                        ->translatedIn($locale)
                        ->withTranslation($locale)
                        ->whereHas('projects', fn (Builder $project) => $project->published()->translatedIn($locale))
                        ->ordered()
                        ->get();

                    $panels['projects'] = [
                        'featured' => array_values($featured->map(fn (Project $project): array => $this->presenter->project($project, $locale))->all()),
                        'categories' => array_values($categories->map(fn (ProjectCategory $category): array => [
                            'name' => (string) $category->translation($locale)?->name,
                            'url' => route('site.projects.index', ['locale' => $locale, 'category' => $category->translation($locale)?->slug]),
                        ])->all()),
                    ];
                }
            }

            return $panels;
        });

        return $panels;
    }

    /**
     * @param  array<int, MenuItem>  $items
     * @return list<ResolvedItem>
     */
    private function resolve(array $items, string $locale): array
    {
        return array_values(array_filter(array_map(fn (MenuItem $item): ?array => $item->resolve($locale), $items)));
    }

    /**
     * @param  ResolvedItem  $item
     */
    private function link(array $item): NavLink
    {
        $active = $item['activeRoute'] !== null
            ? request()->routeIs($item['activeRoute'])
            : $item['url'] !== null && request()->url() === $item['url'];

        return new NavLink($item['label'], $item['url'], $active, $item['newTab'], panel: self::PANELS[$item['system'] ?? ''] ?? null);
    }
}
