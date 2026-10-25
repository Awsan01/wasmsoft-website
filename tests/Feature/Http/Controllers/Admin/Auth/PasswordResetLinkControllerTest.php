<?php

namespace Tests\Feature\Http\Controllers\Admin\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetLinkControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_account_receives_a_reset_link(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $response = $this->post(route('admin.password.email'), ['email' => $user->email]);

        $response->assertRedirect(route('admin.password.request'));
        $response->assertSessionHas('status', __('admin.auth.link_sent'));
        Notification::assertSentTo($user, ResetPasswordNotification::class, fn (ResetPasswordNotification $notification): bool => ! $notification->invitation);
    }

    public function test_unknown_address_gets_the_same_response_and_no_email(): void
    {
        Notification::fake();

        $response = $this->post(route('admin.password.email'), ['email' => 'nobody@example.com']);

        $response->assertRedirect(route('admin.password.request'));
        $response->assertSessionHas('status', __('admin.auth.link_sent'));
        Notification::assertNothingSent();
    }

    public function test_deactivated_account_does_not_receive_a_link(): void
    {
        User::factory()->inactive()->create(['email' => 'former@example.com']);
        Notification::fake();

        $response = $this->post(route('admin.password.email'), ['email' => 'former@example.com']);

        $response->assertSessionHas('status', __('admin.auth.link_sent'));
        Notification::assertNothingSent();
    }

    public function test_invalid_email_is_rejected(): void
    {
        $response = $this->post(route('admin.password.email'), ['email' => 'not-an-email']);

        $response->assertSessionHasErrors(['email' => 'The email field must be a valid email address.']);
    }

    public function test_reset_email_is_written_in_the_recipients_language_and_direction(): void
    {
        $user = User::factory()->create(['name' => 'Awsan', 'locale' => 'ar']);

        $user->notify(new ResetPasswordNotification('token-123'));

        $message = app('mailer')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertSame(__('admin.mail.reset.subject', ['site' => 'Wasmsoft'], 'ar'), $message->getSubject());
        $this->assertStringContainsString('dir="rtl"', $message->getHtmlBody());
        $this->assertStringContainsString('مرحبًا Awsan،', $message->getHtmlBody());
    }

    public function test_reset_email_links_to_the_reset_form_and_escapes_the_name(): void
    {
        $user = User::factory()->make(['name' => '<script>alert(1)</script>', 'email' => 'editor@example.com']);

        $mail = (new ResetPasswordNotification('secret-token'))->toMail($user)->render();

        $this->assertStringContainsString(route('admin.password.reset', ['token' => 'secret-token', 'email' => 'editor@example.com']), $mail);
        $this->assertStringContainsString('&lt;script&gt;', $mail);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $mail);
    }
}
