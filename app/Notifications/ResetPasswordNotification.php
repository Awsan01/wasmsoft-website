<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Password reset link, also used to invite new dashboard users to choose
 * their first password (with a token of the separate "invitations"
 * broker). Queued, so the response time of the "forgot password" form
 * does not reveal whether the account exists. Sent in the recipient's
 * dashboard language.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $token,
        public readonly bool $invitation = false,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $kind = $this->invitation ? 'invitation' : 'reset';
        $site = app(Brand::class)->name();

        if ($this->invitation) {
            // Invitations use their own broker (and token table) with a longer lifetime.
            $url = route('admin.invitation.show', ['token' => $this->token, 'email' => $notifiable->email]);
            $hours = intdiv((int) config('auth.passwords.invitations.expire'), 60);
            $expiry = trans_choice('admin.mail.invitation.expiry', $hours, ['count' => $hours]);
        } else {
            $url = route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
            $expiry = __('admin.mail.expiry', ['minutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]);
        }

        return (new MailMessage)
            ->subject(__("admin.mail.{$kind}.subject", ['site' => $site]))
            ->greeting(__('admin.mail.greeting', ['name' => $notifiable->name]))
            ->line(__("admin.mail.{$kind}.intro", ['site' => $site]))
            ->action(__("admin.mail.{$kind}.action"), $url)
            ->line($expiry)
            ->line(__("admin.mail.{$kind}.ignore"));
    }
}
