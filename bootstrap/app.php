<?php

use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleAdminInertiaRequests;
use App\Http\Middleware\HandleSiteInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetSiteLocale;
use App\Http\Responses\SiteErrorPage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Session-backed public routes (the contact form).
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Dashboard and its sign-in pages: sessions, CSRF, never cached.
            Route::middleware(['web', 'admin.locale', 'admin.inertia', 'cache.headers:private;no_store'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Public pages run without sessions or cookies so they can be cached
            // safely and anonymous traffic never creates session records.
            Route::middleware('site')->group(base_path('routes/site.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->group('site', [
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'site.locale' => SetSiteLocale::class,
            'site.cache' => CachePublicPages::class,
            'site.inertia' => HandleSiteInertiaRequests::class,
            'admin.locale' => SetAdminLocale::class,
            'admin.inertia' => HandleAdminInertiaRequests::class,
            'admin.active' => EnsureUserIsActive::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => ($request->is('api/*') || $request->expectsJson()) && ! $request->header('X-Inertia'),
        );

        // Client errors on public pages are pages of the site (see App\Http\Responses\SiteErrorPage).
        $exceptions->respond(fn (Response $response, Throwable $exception, Request $request): Response => app(SiteErrorPage::class)->render($response, $request) ?? $response);
    })->create();
