<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Content\ContentCache;
use App\Content\SystemPage;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\MenuItem;
use App\Models\Page;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Response;

/**
 * The header and footer menus of the public site.
 */
class NavigationController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ContentCache $cache,
        private readonly AdminPresenter $present,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', MenuItem::class);

        $header = MenuItem::query()->with('page.translations')->where('menu', MenuLocation::Header)->whereNull('parent_id')->ordered()->get();
        $footer = MenuItem::query()->with('children.page.translations')->where('menu', MenuLocation::Footer)->whereNull('parent_id')->ordered()->get();

        return $this->adminPage('navigation/index', __('admin.navigation.title'), [
            'description' => __('admin.navigation.description'),
            'header' => [
                'links' => $this->links($header),
                'addUrl' => route('admin.navigation.create', ['menu' => MenuLocation::Header->value]),
            ],
            'footer' => [
                'groups' => $footer->map(fn (MenuItem $group): array => [
                    'id' => $group->id,
                    'label' => $group->adminLabel(),
                    'visible' => $group->is_visible,
                    'editUrl' => route('admin.navigation.edit', $group),
                    'moveUrl' => route('admin.navigation.move', $group),
                    'addUrl' => route('admin.navigation.create', ['menu' => MenuLocation::Footer->value, 'parent' => $group->id]),
                    'links' => $this->links($group->children),
                ])->values()->all(),
                'addGroupUrl' => route('admin.navigation.create', ['menu' => MenuLocation::Footer->value, 'type' => MenuLinkType::Group->value]),
            ],
            'canReorder' => Gate::allows('reorder', MenuItem::class),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', MenuItem::class);

        $menu = MenuLocation::tryFrom($request->string('menu')->toString()) ?? MenuLocation::Header;
        $group = $menu === MenuLocation::Footer && $request->string('type')->toString() === MenuLinkType::Group->value;

        return $this->form(new MenuItem([
            'menu' => $menu,
            'type' => $group ? MenuLinkType::Group : MenuLinkType::System,
            'parent_id' => $menu === MenuLocation::Footer && ! $group ? ($request->integer('parent') ?: null) : null,
        ]));
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $item = new MenuItem($request->itemAttributes());

        $this->save($item, 'created');

        return redirect()->route('admin.navigation.index')->with('status', __('admin.content.created'));
    }

    public function edit(MenuItem $item): Response
    {
        Gate::authorize('view', $item);

        return $this->form($item);
    }

    public function update(MenuItemRequest $request, MenuItem $item): RedirectResponse
    {
        $item->fill($request->itemAttributes());

        $this->save($item, 'updated');

        return redirect()->route('admin.navigation.index')->with('status', __('admin.content.saved'));
    }

    public function destroy(MenuItem $item): RedirectResponse
    {
        Gate::authorize('delete', $item);

        DB::transaction(function () use ($item): void {
            $this->audit->record(AuditEvent::NavigationUpdated, $item, ['action' => 'deleted']);
            // A column's links go with it (foreign key cascade).
            $item->delete();
        });

        return redirect()->route('admin.navigation.index')->with('status', __('admin.content.deleted'));
    }

    public function move(Request $request, MenuItem $item): RedirectResponse
    {
        Gate::authorize('reorder', MenuItem::class);

        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $item->move($request->input('direction') === 'up' ? -1 : 1);
        $this->audit->record(AuditEvent::NavigationUpdated, $item, ['action' => 'moved']);
        $this->cache->flush();

        return redirect()->route('admin.navigation.index')->with('status', __('admin.content.moved'));
    }

    private function save(MenuItem $item, string $action): void
    {
        DB::transaction(function () use ($item, $action): void {
            $item->save();
            $this->audit->record(AuditEvent::NavigationUpdated, $item, ['action' => $action]);
        });
    }

    /**
     * The links of a menu or footer column, in display order.
     *
     * @param  Collection<int, MenuItem>  $items
     * @return list<array<string, mixed>>
     */
    private function links(Collection $items): array
    {
        return array_values($items->map(fn (MenuItem $link): array => [
            'id' => $link->id,
            'label' => $link->adminLabel(),
            'type' => $link->type->label(),
            'destination' => match ($link->type) {
                MenuLinkType::System => $link->system_page?->label(),
                MenuLinkType::Page => $link->page?->displayTranslation()?->title,
                default => $link->url,
            },
            'isUrl' => $link->type === MenuLinkType::Url,
            'visible' => $link->is_visible,
            'editUrl' => route('admin.navigation.edit', $link),
            'moveUrl' => route('admin.navigation.move', $link),
        ])->all());
    }

    private function form(MenuItem $item): Response
    {
        $isNew = ! $item->exists;
        $isGroup = $item->type === MenuLinkType::Group;
        $title = $isNew ? __($isGroup ? 'admin.navigation.new_group' : 'admin.navigation.new_link') : $item->adminLabel();

        $systemPages = [];

        foreach (SystemPage::cases() as $page) {
            $systemPages[$page->value] = $page->navLabel(app()->getLocale());
        }

        $pages = Page::query()->custom()->with('translations')->get()
            ->mapWithKeys(fn (Page $page): array => [$page->id => (string) $page->displayTranslation()?->title])
            ->all();

        $groups = MenuItem::query()->where('menu', MenuLocation::Footer)->where('type', MenuLinkType::Group)->ordered()->get()
            ->mapWithKeys(fn (MenuItem $group): array => [$group->id => $group->adminLabel()])
            ->all();

        return $this->adminPage('navigation/form', $title, [
            'description' => $item->menu->label(),
            'isGroup' => $isGroup,
            // Links of the footer belong to one of its columns.
            'needsGroup' => $item->menu === MenuLocation::Footer && ! $isGroup,
            'submit' => $this->present->formAction($item, 'admin.navigation'),
            'form' => [
                'menu' => $item->menu->value,
                'type' => $item->type->value,
                'system_page' => $item->system_page->value ?? '',
                'page_id' => (string) ($item->page_id ?? ''),
                'url' => (string) $item->url,
                'opens_in_new_tab' => $item->opens_in_new_tab,
                'label' => $this->present->localized($item->label),
                'parent_id' => (string) ($item->parent_id ?? ''),
                'is_visible' => $item->is_visible,
            ],
            'linkTypes' => array_map(
                fn (MenuLinkType $type): array => ['value' => $type->value, 'label' => $type->label()],
                [MenuLinkType::System, MenuLinkType::Page, MenuLinkType::Url],
            ),
            'systemPageOptions' => $this->present->options($systemPages),
            'pageOptions' => $this->present->options($pages),
            'groupOptions' => $this->present->options($groups),
            'labelHint' => $isGroup ? __('admin.navigation.label_hint_required') : __('admin.navigation.label_hint'),
            'destroy' => ! $isNew && Gate::allows('delete', $item)
                ? $this->present->deletion(
                    route('admin.navigation.destroy', $item),
                    $title,
                    $isGroup ? __('admin.navigation.delete_group_text') : __('admin.navigation.delete_text'),
                )
                : null,
        ], [
            ['label' => __('admin.navigation.title'), 'url' => route('admin.navigation.index')],
            ['label' => $title, 'url' => null],
        ]);
    }
}
