<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
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
