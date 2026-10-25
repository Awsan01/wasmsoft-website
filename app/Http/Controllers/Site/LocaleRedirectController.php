<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sends visitors of "/" to the best enabled locale for their browser.
 */
class LocaleRedirectController extends Controller
{
    public function __invoke(Request $request, Locales $locales): RedirectResponse
    {
        $response = redirect()->route('site.home', ['locale' => $locales->negotiate($request)]);

        // The target depends on Accept-Language, so shared caches must not reuse it blindly.
        $response->headers->set('Vary', 'Accept-Language');
        $response->setPrivate();

        return $response;
    }
}
