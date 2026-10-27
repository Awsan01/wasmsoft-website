<?php

namespace Tests\Feature;

use App\Auth\Role;
use App\Models\User;
use App\Navigation\SiteNavigation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['en', 'ltr'])]
    #[TestWith(['ar', 'rtl'])]
    #[TestWith(['tr', 'ltr'])]
    public function test_a_missing_public_page_is_explained_in_the_language_of_the_url(string $locale, string $direction): void
    {
        $response = $this->get("/{$locale}/no-such-page");

        $response->assertNotFound();
        $response->assertSee('<html lang="'.$locale.'" dir="'.$direction.'">', false);
        $response->assertSee('<meta data-inertia="robots" name="robots" content="noindex, nofollow">', false);
        // A page of the site, with its menus: what happened, and the way home.
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/error')
            ->where('status', 404)
            ->where('title', __('site.errors.404.title', [], $locale))
            ->where('message', __('site.errors.404.message', [], $locale))
            ->where('site.homeUrl', url("/{$locale}"))
            ->has('navigation.header', 3));
    }

    public function test_without_a_language_in_the_url_the_browser_language_is_used(): void
    {
        $this->get('/no-such-page', ['Accept-Language' => 'tr-TR,tr;q=0.9'])
            ->assertNotFound()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('title', __('site.errors.404.title', [], 'tr'))
                ->where('locale.code', 'tr'));
    }

    public function test_a_link_to_a_missing_page_inside_the_site_shows_the_error_page(): void
    {
        $version = $this->get('/en')->inertiaPage()['version'];

        $this->get('/en/no-such-page', ['X-Inertia' => 'true', 'X-Inertia-Version' => $version, 'X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'site/error')
            ->assertJsonPath('props.status', 404);
    }

    public function test_a_page_that_fails_to_build_falls_back_to_the_self_contained_error_page(): void
    {
        config(['app.debug' => false]);
        // A broken shared setting makes the site's own error page fail to build; the plain one remains.
        $this->mock(SiteNavigation::class, function ($mock): void {
            $mock->shouldReceive('header')->andThrow(new RuntimeException('navigation unavailable'));
        });

        $this->get('/en/no-such-page')
            ->assertNotFound()
            ->assertSee(__('site.errors.404.title'))
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertDontSee('navigation unavailable');
    }

    public function test_dashboard_errors_lead_back_to_the_dashboard(): void
    {
        $viewer = User::factory()->role(Role::Viewer)->create();

        $this->actingAs($viewer)->get(route('admin.users.index'))
            ->assertForbidden()
            ->assertSee(__('site.errors.403.title'))
            ->assertSee(__('site.errors.dashboard'))
            ->assertSee('href="'.url('/admin').'"', false);
    }

    public function test_server_errors_reveal_nothing_technical(): void
    {
        config(['app.debug' => false]);
        Route::get('/_test/failure', fn () => throw new RuntimeException('database password is hunter2'));

        $this->get('/_test/failure')
            ->assertInternalServerError()
            ->assertSee(__('site.errors.500.title'))
            ->assertDontSee('hunter2');
    }

    public function test_other_client_errors_get_the_branded_page_with_their_code(): void
    {
        config(['app.debug' => false]);

        $this->delete('/tr')
            ->assertMethodNotAllowed()
            ->assertSee(__('site.errors.4xx.title', [], 'tr'))
            ->assertSee(__('site.errors.code', ['code' => 405], 'tr'));
    }

    public function test_other_server_errors_get_the_branded_page_with_their_code(): void
    {
        config(['app.debug' => false]);
        Route::get('/_test/gateway', fn () => abort(502));

        $this->get('/_test/gateway')
            ->assertStatus(502)
            ->assertSee(__('site.errors.5xx.title'))
            ->assertSee(__('site.errors.code', ['code' => 502]));
    }

    public function test_an_upload_over_the_server_limit_is_explained(): void
    {
        config(['app.debug' => false]);
        Route::post('/admin/_test/upload', fn () => throw new PostTooLargeException);

        $this->post('/admin/_test/upload')
            ->assertStatus(413)
            ->assertSee(__('site.errors.413.title'))
            ->assertSee(__('site.errors.dashboard'));
    }

    #[TestWith([500])]
    #[TestWith([503])]
    public function test_server_and_maintenance_pages_render_without_the_database(int $code): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $html = view("errors.{$code}")->render();

        $this->assertStringContainsString(e(__("site.errors.{$code}.title")), $html);
        $this->assertSame(0, $queries);
    }

    public function test_rate_limited_visitors_get_a_clear_message(): void
    {
        config(['wasmsoft.contact.max_per_hour' => 1]);

        $this->post('/ar/contact', []);

        $this->post('/ar/contact', [])
            ->assertTooManyRequests()
            ->assertSee(__('site.errors.429.title', [], 'ar'));
    }
}
