<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{Role}>
     */
    public static function roles(): array
    {
        return collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => [$role]])->all();
    }

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    #[DataProvider('roles')]
    public function test_every_role_can_open_the_overview(Role $role): void
    {
        $user = User::factory()->role($role)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee($role->label());
        $response->assertHeader('Cache-Control', 'no-store, private');
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_deactivated_user_is_signed_out_with_an_explanation(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('admin.auth.deactivated')]);
        $this->assertGuest();
    }

    public function test_sidebar_only_lists_modules_the_user_may_open(): void
    {
        $user = User::factory()->role(Role::Viewer)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertSee(__('admin.nav.overview'));
        $response->assertDontSee(__('admin.nav.users'));
        $response->assertDontSee(__('admin.nav.settings'));
    }

    public function test_user_name_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
