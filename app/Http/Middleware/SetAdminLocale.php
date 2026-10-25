<?php

namespace App\Http\Middleware;

use App\Localization\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chooses the dashboard language: the signed-in user's preference, then
 * the language picked on the sign-in pages, then the browser's language.
 *
 * Every supported locale is available in the dashboard, even ones not yet
 * enabled on the public site, so editors can prepare translations.
 */
class SetAdminLocale
{
    public function __construct(private readonly Locales $locales) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale;

        if (! $this->locales->isSupported($locale)) {
            $locale = $request->session()->get('admin_locale');
        }

        if (! $this->locales->isSupported($locale)) {
            $locale = $this->locales->negotiate($request, $this->locales->supported());
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
