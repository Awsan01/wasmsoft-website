<?php

namespace App\Http\Middleware;

use App\Localization\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the locale from the {locale} URL prefix of public routes.
 *
 * The locale becomes the default for generated URLs, so route() keeps
 * visitors in their language across navigation, pagination and filters.
 * Disabled locales return 404 rather than silently showing another language.
 */
class SetSiteLocale
{
    public function __construct(private readonly Locales $locales) {}

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $locale = $route instanceof Route ? $route->parameter('locale') : null;

        abort_unless($route instanceof Route && is_string($locale) && $this->locales->isEnabled($locale), 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        // Controllers receive only their own parameters (slug…), not the locale prefix.
        $route->forgetParameter('locale');

        return $next($request);
    }
}
