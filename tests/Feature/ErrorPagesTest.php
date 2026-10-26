<?php

namespace Tests\Feature;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
        $response->assertSee(__('site.errors.404.title', [], $locale));
        $response->assertSee('href="'.url("/{$locale}").'"', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_without_a_language_in_the_url_the_browser_language_is_used(): void
    {
        $this->get('/no-such-page', ['Accept-Language' => 'tr-TR,tr;q=0.9'])
            ->assertNotFound()
            ->assertSee(__('site.errors.404.title', [], 'tr'));
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
