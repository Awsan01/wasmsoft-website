<?php

use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetSiteLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Session-backed public routes (the contact form).
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
