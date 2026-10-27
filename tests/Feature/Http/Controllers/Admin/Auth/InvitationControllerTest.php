<?php

namespace Tests\Feature\Http\Controllers\Admin\Auth;

use App\Audit\AuditEvent;
use App\Auth\Invitations;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InvitationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function invite(User $user): string
    {
        return app(Invitations::class)->broker()->createToken($user);
    }

    /**
     * @return array<string, string>
     */
    private function acceptance(User $user, string $token): array
    {
        return [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Correct-Horse-42',
            'password_confirmation' => 'Correct-Horse-42',
        ];
    }

    public function test_the_invited_person_chooses_a_password_and_signs_in(): void
    {
        $user = User::factory()->editor()->unverified()->create();
        $token = $this->invite($user);

        $this->get(route('admin.invitation.show', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/auth/new-password')
                ->where('title', __('admin.auth.invitation_title'))
                ->where('purpose', 'invitation')
                ->where('email', $user->email)
            );

        $this->post(route('admin.invitation.store'), $this->acceptance($user, $token))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('status', __('admin.auth.invitation_accepted'));

        $user->refresh();
        $this->assertTrue(Hash::check('Correct-Horse-42', $user->password));
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('user_invitation_tokens', ['email' => $user->email]);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::InvitationAccepted->value, 'subject_id' => $user->id]);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'Correct-Horse-42'])->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_password_reset_link_is_not_an_invitation(): void
    {
        $user = User::factory()->unverified()->create();
        $resetToken = Password::broker()->createToken($user);

        $this->post(route('admin.invitation.store'), $this->acceptance($user, $resetToken))
            ->assertSessionHasErrors(['email' => __('admin.auth.invitation_invalid')]);
    }

    public function test_an_expired_invitation_is_refused(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $this->invite($user);

        $this->travel((int) config('auth.passwords.invitations.expire') + 1)->minutes();

        $this->post(route('admin.invitation.store'), $this->acceptance($user, $token))->assertSessionHasErrors('email');
    }

    public function test_a_deactivated_account_cannot_accept_its_invitation(): void
    {
        $user = User::factory()->unverified()->inactive()->create();
        $token = $this->invite($user);

        $this->post(route('admin.invitation.store'), $this->acceptance($user, $token))->assertSessionHasErrors('email');
    }

    public function test_the_invitation_mail_links_to_the_invitation_page(): void
    {
        $user = User::factory()->unverified()->create();

        $html = (string) (new ResetPasswordNotification('invite-token', invitation: true))->toMail($user)->render();

        $this->assertStringContainsString(e(route('admin.invitation.show', ['token' => 'invite-token', 'email' => $user->email])), $html);
        $this->assertStringContainsString(trans_choice('admin.mail.invitation.expiry', 72, ['count' => 72]), $html);
    }
}
