<?php

namespace App\Auth;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use LogicException;

/**
 * Invitations of new dashboard users to choose their first password.
 *
 * Administrators never set or see passwords: a new account gets a random
 * one nobody knows and an invitation link of the "invitations" broker
 * (config/auth.php), which lives longer than a password reset link and
 * is stored in its own table. Accepting it confirms the email address.
 */
class Invitations
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Send an invitation (again). Returns false when one was sent less than
     * a minute ago.
     */
    public function send(User $user): bool
    {
        $broker = $this->broker();

        if ($broker->getRepository()->recentlyCreatedToken($user)) {
            return false;
        }

        $user->notify(new ResetPasswordNotification($broker->createToken($user), invitation: true));
        $this->audit->record(AuditEvent::InvitationSent, $user);

        return true;
    }

    /**
     * The person has not accepted the invitation yet.
     */
    public function isPending(User $user): bool
    {
        return $user->email_verified_at === null;
    }

    public function broker(): PasswordBroker
    {
        $broker = Password::broker('invitations');

        if (! $broker instanceof PasswordBroker) {
            throw new LogicException('The "invitations" password broker must be configured in config/auth.php.');
        }

        return $broker;
    }
}
