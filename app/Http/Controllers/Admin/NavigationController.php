<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Content\ContentCache;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\MenuItem;
use App\Models\Page;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * The header and footer menus of the public site.
 */
class NavigationController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ContentCache $cache,
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', MenuItem::class);

        return view('admin.navigation.index', [
            'header' => MenuItem::query()->with('page.translations')->where('menu', MenuLocation::Header)->whereNull('parent_id')->ordered()->get(),
            'footer' => MenuItem::query()->with('children.page.translations')->where('menu', MenuLocation::Footer)->whereNull('parent_id')->ordered()->get(),
            'canReorder' => Gate::allows('reorder', MenuItem::class),
        ]);
    }

    public function create(Request $request): View
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

    public function edit(MenuItem $item): View
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

    private function form(MenuItem $item): View
    {
        $systemPages = [];

        foreach (SystemPage::cases() as $page) {
            $systemPages[$page->value] = $page->navLabel(app()->getLocale());
        }

        return view('admin.navigation.form', [
            'item' => $item,
            'systemPages' => $systemPages,
            'pages' => Page::query()->custom()->with('translations')->get()
                ->mapWithKeys(fn (Page $page): array => [$page->id => (string) $page->displayTranslation()?->title])
                ->all(),
            'groups' => MenuItem::query()->where('menu', MenuLocation::Footer)->where('type', MenuLinkType::Group)->ordered()->get()
                ->mapWithKeys(fn (MenuItem $group): array => [$group->id => $group->adminLabel()])
                ->all(),
        ]);
    }
}
