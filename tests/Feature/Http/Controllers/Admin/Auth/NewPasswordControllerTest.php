<?php

namespace Tests\Feature\Http\Controllers\Admin\Auth;

use App\Audit\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class NewPasswordControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const NEW_PASSWORD = 'Brand-New-Password-7';

    public function test_reset_form_renders_with_the_token_and_email(): void
    {
        $response = $this->get(route('admin.password.reset', ['token' => 'abc123', 'email' => 'editor@example.com']));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/auth/new-password')
            ->where('purpose', 'reset')
            ->where('token', 'abc123')
            ->where('email', 'editor@example.com')
            ->where('urls.store', route('admin.password.store'))
        );
    }

    public function test_valid_token_sets_the_new_password_and_signs_out_remembered_sessions(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-remember-token']);
        $token = Password::createToken($user);

        $response = $this->post(route('admin.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('status', __('passwords.reset'));
        $user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->password));
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::PasswordReset->value, 'user_id' => $user->id]);
    }

    public function test_invalid_token_is_rejected_and_the_password_is_unchanged(): void
    {
        $user = User::factory()->create(['password' => 'Original-Password-1']);

        $response = $this->post(route('admin.password.store'), [
            'token' => 'forged-token',
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $response->assertSessionHasErrors(['email' => __('passwords.token')]);
        $this->assertTrue(Hash::check('Original-Password-1', $user->fresh()->password));
    }

    public function test_unknown_address_gets_the_same_error_as_an_invalid_token(): void
    {
        $response = $this->post(route('admin.password.store'), [
            'token' => 'any-token',
            'email' => 'nobody@example.com',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $response->assertSessionHasErrors(['email' => __('passwords.token')]);
    }

    public function test_weak_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('admin.password.store'), [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password' => 'The password field must be at least 12 characters.']);
    }

    public function test_mismatched_confirmation_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('admin.password.store'), [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => 'Something-Else-9',
        ]);

        $response->assertSessionHasErrors(['password' => 'The password field confirmation does not match.']);
    }
}
