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
 * their first password. Queued, so the response time of the "forgot
 * password" form does not reveal whether the account exists. Sent in the
 * recipient's dashboard language.
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
        $url = route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $site = app(Brand::class)->name();

        return (new MailMessage)
            ->subject(__("admin.mail.{$kind}.subject", ['site' => $site]))
            ->greeting(__('admin.mail.greeting', ['name' => $notifiable->name]))
            ->line(__("admin.mail.{$kind}.intro", ['site' => $site]))
            ->action(__("admin.mail.{$kind}.action"), $url)
            ->line(__('admin.mail.expiry', ['minutes' => $minutes]))
            ->line(__("admin.mail.{$kind}.ignore"));
    }
}
