<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Switches the dashboard language. Signed-in users keep the choice on their
 * account; guests (sign-in pages) keep it in the session.
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, Locales $locales): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($locales->supported())],
        ]);

        $request->user()?->forceFill(['locale' => $validated['locale']])->save();
        $request->session()->put('admin_locale', $validated['locale']);

        // The session's previous URL is always one of our own pages (never the Referer header).
        return redirect()->to($request->session()->previousUrl() ?? route('admin.dashboard'));
    }
}
