<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    use RendersAdminPages;

    public function create(): Response
    {
        return $this->adminPage('auth/forgot-password', __('admin.auth.forgot_title'), [
            'urls' => ['store' => route('admin.password.email'), 'login' => route('admin.login')],
        ]);
    }

    /**
     * The response is identical whether or not the address belongs to an
     * active account, so the form cannot be used to discover accounts.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        Password::sendResetLink([
            'email' => Str::lower($request->string('email')->trim()->toString()),
            'is_active' => true,
        ]);

        return redirect()->route('admin.password.request')->with('status', __('admin.auth.link_sent'));
    }
}
