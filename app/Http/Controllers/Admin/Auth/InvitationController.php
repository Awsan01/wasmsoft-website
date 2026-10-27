<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Invitations;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

/**
 * Accepting an invitation: the new user chooses their first password.
 */
class InvitationController extends Controller
{
    use RendersAdminPages;

    public function show(Request $request, string $token): Response
    {
        return $this->adminPage('auth/new-password', __('admin.auth.invitation_title'), [
            'purpose' => 'invitation',
            'token' => $token,
            'email' => $request->string('email')->toString(),
            'urls' => ['store' => route('admin.invitation.store')],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, Invitations $invitations, AuditLogger $audit): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = $invitations->broker()->reset(
            [
                'email' => Str::lower($request->string('email')->trim()->toString()),
                'password' => $request->string('password')->toString(),
                'token' => $request->string('token')->toString(),
                'is_active' => true,
            ],
            function (User $user, string $password) use ($audit): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    // The invitation reached this address.
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                $audit->record(AuditEvent::InvitationAccepted, $user, actor: $user);
            },
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', __('admin.auth.invitation_accepted'));
        }

        // Unknown address, expired or used link: one message, no account discovery.
        throw ValidationException::withMessages(['email' => __('admin.auth.invitation_invalid')]);
    }
}
