<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\SystemPage;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasLocalizedText;
use App\Models\Concerns\Sortable;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An entry of a site menu: a fixed page, a custom page, an external link,
 * or (footer only) a group heading whose children are the links of one
 * footer column. Ordered among its siblings.
 *
 * @property int $id
 * @property MenuLocation $menu
 * @property int|null $parent_id
 * @property MenuLinkType $type
 * @property SystemPage|null $system_page
 * @property int|null $page_id
 * @property string|null $url
 * @property array<string, string>|null $label
 * @property bool $opens_in_new_tab
 * @property bool $is_visible
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MenuItem|null $parent
 * @property-read Collection<int, MenuItem> $children
 * @property-read Page|null $page
 */
#[Fillable(['menu', 'parent_id', 'type', 'system_page', 'page_id', 'url', 'label', 'opens_in_new_tab', 'is_visible'])]
class MenuItem extends Model implements Auditable
{
    use FlushesContentCache, HasLocalizedText, Sortable;

    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'parent_id' => null,
        'system_page' => null,
        'page_id' => null,
        'url' => null,
        'label' => null,
        'opens_in_new_tab' => false,
        'is_visible' => true,
    ];

    protected function casts(): array
    {
        return [
            'menu' => MenuLocation::class,
            'type' => MenuLinkType::class,
            'system_page' => SystemPage::class,
            'label' => 'array',
            'opens_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Siblings: the same menu and the same group.
     *
     * @return Builder<static>
     */
    protected function sortableGroup(): Builder
    {
        return static::query()->where('menu', $this->menu)->where('parent_id', $this->parent_id);
    }

    /**
     * What this item shows in $locale, or null when it has nothing to show
     * there: a page not published in that language, or a link or group
     * without a label in that language. `activeRoute` is the route pattern
     * under which the link is the current page (null: compared by URL);
     * `system` names the fixed page a link points to.
     *
     * @return array{label: string, url: ?string, newTab: bool, activeRoute: ?string, system: ?string}|null
     */
    public function resolve(string $locale): ?array
    {
        $label = $this->localized('label', $locale);

        switch ($this->type) {
            case MenuLinkType::System:
                return $this->system_page === null ? null : [
                    'label' => $label ?? $this->system_page->navLabel($locale),
                    'url' => route($this->system_page->routeName(), ['locale' => $locale]),
                    'newTab' => false,
                    'activeRoute' => $this->system_page->activePattern(),
                    'system' => $this->system_page->value,
                ];

            case MenuLinkType::Page:
                $url = $this->page?->publicUrl($locale);

                return $url === null ? null : [
                    'label' => $label ?? (string) $this->page->translation($locale)?->title,
                    'url' => $url,
                    'newTab' => false,
                    'activeRoute' => null,
                    'system' => null,
                ];

            case MenuLinkType::Url:
                return $label === null || $this->url === null ? null : [
                    'label' => $label,
                    'url' => $this->url,
                    'newTab' => $this->opens_in_new_tab,
                    'activeRoute' => null,
                    'system' => null,
                ];

            case MenuLinkType::Group:
                return $label === null ? null : ['label' => $label, 'url' => null, 'newTab' => false, 'activeRoute' => null, 'system' => null];
        }
    }

    /**
     * How the item is named in the dashboard.
     */
    public function adminLabel(): string
    {
        return $this->displayText('label')
            ?? match ($this->type) {
                MenuLinkType::System => $this->system_page?->navLabel(app()->getLocale()) ?? '—',
                MenuLinkType::Page => $this->page?->displayTranslation()->title ?? __('admin.pages.untitled'),
                MenuLinkType::Url => (string) $this->url,
                MenuLinkType::Group => __('admin.navigation.untitled_group'),
            };
    }

    public function auditLabel(): string
    {
        return $this->adminLabel();
    }
}
