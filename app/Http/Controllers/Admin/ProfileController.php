<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Localization\Locales;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Response;
use LogicException;

/**
 * The signed-in user's own account: name, dashboard language and password.
 * The email address and role are changed by an administrator.
 */
class ProfileController extends Controller
{
    use RendersAdminPages;

    public function __construct(private readonly AuditLogger $audit) {}

    public function edit(Request $request, Locales $locales, AdminPresenter $present): Response
    {
        $user = $this->authenticated($request);

        return $this->adminPage('profile/edit', __('admin.profile.title'), [
            'account' => ['email' => $user->email, 'role' => $user->role->label()],
            'form' => ['name' => $user->name, 'locale' => (string) $user->locale],
            'languageOptions' => $present->options(array_combine($locales->supported(), array_map($locales->nativeName(...), $locales->supported()))),
            'submit' => ['url' => route('admin.profile.update'), 'method' => 'put'],
            'passwordSubmit' => ['url' => route('admin.profile.password'), 'method' => 'put'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->authenticated($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', Rule::in(app(Locales::class)->supported())],
        ], attributes: [
            'name' => __('admin.users.fields.name'),
            'locale' => __('admin.users.fields.locale'),
        ]);

        $user->forceFill(['name' => $validated['name'], 'locale' => $validated['locale'] ?? null]);

        if ($user->isDirty()) {
            $changed = array_keys($user->getDirty());
            $user->save();
            $this->audit->record(AuditEvent::UserUpdated, $user, ['changed' => $changed]);
        }

        return redirect()->route('admin.profile.edit')->with('status', __('admin.content.saved'));
    }

    /**
     * Change the password. Other devices are signed out: their sessions no
     * longer match the password (auth.session middleware) and the new
     * remember token invalidates "remember me" cookies.
     */
    public function password(Request $request): RedirectResponse
    {
        $user = $this->authenticated($request);

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'max:255', 'confirmed', 'different:current_password', Password::defaults()],
        ], attributes: [
            'current_password' => __('admin.profile.current_password'),
            'password' => __('admin.auth.new_password'),
        ]);

        $user->forceFill([
            'password' => $request->string('password')->toString(),
            'remember_token' => Str::random(60),
        ])->save();

        $this->audit->record(AuditEvent::PasswordChanged, $user);

        return redirect()->route('admin.profile.edit')->with('status', __('admin.profile.password_changed'));
    }

    private function authenticated(Request $request): User
    {
        return $request->user() ?? throw new LogicException('The profile is only available to signed-in users.');
    }
}
