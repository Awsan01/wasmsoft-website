<?php

namespace Tests\Feature\Http\Controllers\Admin\Auth;

use App\Audit\AuditEvent;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const PASSWORD = 'Correct-Horse-42';

    public function test_login_page_renders_for_guests_without_being_cached_or_indexed(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('name="email"', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_valid_credentials_sign_in_and_record_the_login(): void
    {
        $this->freezeTime();
        $user = User::factory()->editor()->create(['password' => self::PASSWORD]);

        $response = $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(now()->toDateTimeString(), $user->fresh()->last_login_at?->toDateTimeString());
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::Login->value, 'user_id' => $user->id]);
    }

    public function test_email_is_matched_case_insensitively(): void
    {
        $user = User::factory()->create(['email' => 'editor@example.com', 'password' => self::PASSWORD]);

        $this->post(route('admin.login.store'), ['email' => ' Editor@Example.COM ', 'password' => self::PASSWORD]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected_with_a_generic_message_and_audited_without_the_password(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'Wrong-Password-1',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();

        $log = AuditLog::query()->sole();
        $this->assertSame(AuditEvent::LoginFailed, $log->event);
        $this->assertSame(['email' => $user->email], $log->properties);
        $this->assertStringNotContainsString('Wrong-Password-1', json_encode($log->getAttributes(), JSON_THROW_ON_ERROR));
    }

    public function test_deactivated_account_gets_the_same_message_as_wrong_credentials(): void
    {
        $user = User::factory()->inactive()->create(['password' => self::PASSWORD]);

        $response = $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();
    }

    public function test_repeated_failures_lock_the_email_out_even_with_the_right_password(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => "Wrong-Password-{$attempt}"]);
        }
        $response = $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::Lockout->value]);
    }

    public function test_missing_fields_are_reported(): void
    {
        $response = $this->post(route('admin.login.store'), []);

        $response->assertSessionHasErrors([
            'email' => 'The email field is required.',
            'password' => 'The password field is required.',
        ]);
    }

    public function test_signed_in_user_is_sent_from_the_login_page_to_the_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_sign_out_ends_the_session_and_is_audited(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::Logout->value, 'user_id' => $user->id]);
    }

    public function test_sign_out_requires_a_post_request(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin/logout');

        $response->assertMethodNotAllowed();
        $this->assertAuthenticated();
    }
}
