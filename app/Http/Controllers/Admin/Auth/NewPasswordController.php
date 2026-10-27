<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class NewPasswordController extends Controller
{
    use RendersAdminPages;

    public function create(Request $request, string $token): Response
    {
        return $this->adminPage('auth/new-password', __('admin.auth.reset_title'), [
            'purpose' => 'reset',
            'token' => $token,
            'email' => $request->string('email')->toString(),
            'urls' => ['store' => route('admin.password.store')],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => Str::lower($request->string('email')->trim()->toString()),
                'password' => $request->string('password')->toString(),
                'token' => $request->string('token')->toString(),
                'is_active' => true,
            ],
            function (User $user, string $password): void {
                // A new remember token signs out "remember me" sessions on other devices.
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', __('passwords.reset'));
        }

        // An unknown address gets the same message as a bad token (no account discovery).
        throw ValidationException::withMessages(['email' => __('passwords.token')]);
    }
}
