<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class NavigationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function link(array $overrides = []): array
    {
        return [
            'menu' => 'header',
            'type' => 'system',
            'system_page' => 'contact',
            'page_id' => '',
            'url' => '',
            'label' => ['ar' => '', 'en' => '', 'tr' => ''],
            'opens_in_new_tab' => '0',
            'is_visible' => '1',
            ...$overrides,
        ];
    }

    public function test_editors_see_both_menus_with_the_default_links(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->get(route('admin.navigation.index'));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('admin/navigation/index')->where('canReorder', true));
        $this->assertContains(__('site.nav.services'), array_column($response->inertiaProps('header.links'), 'label'));
        $this->assertContains(__('site.footer.company'), array_column($response->inertiaProps('footer.groups'), 'label'));
    }

    public function test_a_header_link_to_a_custom_page_is_added_and_audited(): void
    {
        $page = Page::factory()->published()->translated(['en'], ['en' => ['title' => 'Careers', 'slug' => 'careers']])->create();

        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.navigation.store'), $this->link(['type' => 'page', 'page_id' => (string) $page->id]))
            ->assertRedirect(route('admin.navigation.index'));

        $item = MenuItem::query()->where('type', MenuLinkType::Page)->sole();
        $this->assertSame($page->id, $item->page_id);
        $this->assertNull($item->system_page);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::NavigationUpdated->value, 'subject_type' => 'menu_item', 'subject_id' => $item->id]);
    }

    #[TestWith(['javascript:alert(1)'])]
    #[TestWith(['ftp://files.example.com'])]
    #[TestWith(['/admin'])]
    public function test_external_links_must_be_web_addresses(string $url): void
    {
        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.navigation.store'), $this->link(['type' => 'url', 'url' => $url, 'label' => ['ar' => '', 'en' => 'Partner', 'tr' => '']]))
            ->assertSessionHasErrors('url');
    }

    public function test_external_links_and_columns_need_a_label(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->post(route('admin.navigation.store'), $this->link(['type' => 'url', 'url' => 'https://partner.example']))
            ->assertSessionHasErrors('label.en');
        $this->actingAs($editor)->post(route('admin.navigation.store'), $this->link(['menu' => 'footer', 'type' => 'group']))
            ->assertSessionHasErrors('label.en');
    }

    public function test_footer_links_belong_to_a_column_and_columns_only_exist_in_the_footer(): void
    {
        $editor = User::factory()->editor()->create();
        $column = MenuItem::query()->where('type', MenuLinkType::Group)->firstOrFail();

        $this->actingAs($editor)->post(route('admin.navigation.store'), $this->link(['menu' => 'footer']))
            ->assertSessionHasErrors('parent_id');
        $this->actingAs($editor)->post(route('admin.navigation.store'), $this->link(['type' => 'group', 'label' => ['ar' => '', 'en' => 'More', 'tr' => '']]))
            ->assertSessionHasErrors('type');

        $this->actingAs($editor)->post(route('admin.navigation.store'), $this->link(['menu' => 'footer', 'parent_id' => (string) $column->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame(MenuLocation::Footer, MenuItem::query()->latest('id')->firstOrFail()->menu);
    }

    public function test_a_column_with_links_keeps_its_type_and_deleting_it_removes_its_links(): void
    {
        $editor = User::factory()->editor()->create();
        $column = MenuItem::query()->where('type', MenuLinkType::Group)->firstOrFail();
        $linkIds = $column->children()->pluck('id')->all();

        $this->actingAs($editor)->put(route('admin.navigation.update', $column), $this->link(['type' => 'system', 'system_page' => 'home']))
            ->assertSessionHasErrors('type');

        $this->actingAs($editor)->delete(route('admin.navigation.destroy', $column))->assertRedirect(route('admin.navigation.index'));

        $this->assertModelMissing($column);
        $this->assertSame(0, MenuItem::query()->whereKey($linkIds)->count());
    }

    public function test_links_are_reordered_within_their_menu(): void
    {
        $header = MenuItem::query()->where('menu', MenuLocation::Header)->ordered()->pluck('id')->all();

        $this->actingAs(User::factory()->editor()->create())->patch(route('admin.navigation.move', $header[1]), ['direction' => 'up']);

        $this->assertSame([$header[1], $header[0], ...array_slice($header, 2)], MenuItem::query()->where('menu', MenuLocation::Header)->ordered()->pluck('id')->all());
    }

    #[TestWith([Role::Author])]
    #[TestWith([Role::Viewer])]
    public function test_roles_without_the_navigation_permission_are_refused(Role $role): void
    {
        $user = User::factory()->role($role)->create();
        $item = MenuItem::query()->firstOrFail();

        $this->actingAs($user)->get(route('admin.navigation.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.navigation.store'), $this->link())->assertForbidden();
        $this->actingAs($user)->put(route('admin.navigation.update', $item), $this->link())->assertForbidden();
        $this->actingAs($user)->delete(route('admin.navigation.destroy', $item))->assertForbidden();
    }
}
