<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_choice_is_kept_in_the_session_and_switches_the_sign_in_page_to_rtl(): void
    {
        $this->get(route('admin.login'));

        $response = $this->post(route('admin.locale'), ['locale' => 'ar']);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('admin_locale', 'ar');
        $this->get(route('admin.login'))->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_signed_in_user_choice_is_saved_on_the_account(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->post(route('admin.locale'), ['locale' => 'tr']);

        $this->assertSame('tr', $user->fresh()->locale);
        $this->actingAs($user->fresh())->get(route('admin.dashboard'))->assertSee('<html lang="tr" dir="ltr">', false);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)->post(route('admin.locale'), ['locale' => 'fr']);

        $response->assertSessionHasErrors('locale');
        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_dashboard_falls_back_to_the_browser_language(): void
    {
        $user = User::factory()->create(['locale' => null]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'), ['Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.5']);

        $response->assertSee('<html lang="tr" dir="ltr">', false);
    }

    public function test_locale_switch_never_redirects_to_another_site(): void
    {
        $response = $this->post(route('admin.locale'), ['locale' => 'en'], ['Referer' => 'https://evil.example/phish']);

        $response->assertRedirect(route('admin.dashboard'));
    }
}
