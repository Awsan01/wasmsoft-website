<?php

namespace App\Listeners;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

/**
 * Records authentication activity in the audit log. Registered through
 * event discovery (one handler method per event type).
 *
 * Only the attempted email address is kept for failures; submitted
 * passwords never leave the authentication layer.
 */
class RecordAuthenticationEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record(AuditEvent::Login, $event->user, ['remember' => $event->remember], $event->user);
        }
    }

    public function handleFailed(Failed $event): void
    {
        $this->audit->record(
            AuditEvent::LoginFailed,
            $event->user instanceof User ? $event->user : null,
            ['email' => $this->attemptedEmail($event->credentials['email'] ?? null)],
        );
    }

    public function handleLockout(Lockout $event): void
    {
        $this->audit->record(AuditEvent::Lockout, properties: [
            'email' => $this->attemptedEmail($event->request->input('email')),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record(AuditEvent::Logout, $event->user, actor: $event->user);
        }
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record(AuditEvent::PasswordReset, $event->user, actor: $event->user);
        }
    }

    private function attemptedEmail(mixed $email): string
    {
        return Str::limit(Str::lower(trim(is_string($email) ? $email : '')), 120, '');
    }
}
