<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    use RendersAdminPages;

    public function create(): Response
    {
        return $this->adminPage('auth/login', __('admin.auth.sign_in'), [
            'urls' => ['store' => route('admin.login.store'), 'forgot' => route('admin.password.request')],
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        // A new session id on privilege change prevents session fixation.
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // The dashboard pages kept in this browser's history can no longer be read.
        Inertia::clearHistory();

        return redirect()->route('admin.login')->with('status', __('admin.auth.signed_out'));
    }
}
