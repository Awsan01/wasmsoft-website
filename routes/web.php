<?php

/*
|--------------------------------------------------------------------------
| Session-backed routes ("web" middleware group: cookies, session, CSRF)
|--------------------------------------------------------------------------
|
| The public contact form: it needs CSRF protection and validation
| feedback, so it cannot use the session-less "site" group. The form at the
| end of the home page (a session-less page) fetches its spam-guard token
| here once a visitor starts filling it in; that also starts the session
| whose CSRF cookie browsers without Sec-Fetch-Site need to send it.
| The dashboard has its own file (routes/admin.php).
|
*/

use App\Http\Controllers\Site\ContactController;
use App\Localization\Locales;
use Illuminate\Support\Facades\Route;

Route::prefix('{locale}')
    ->where(['locale' => app(Locales::class)->routePattern()])
    ->middleware(['site.locale', 'site.inertia'])
    ->name('site.')
    ->group(function (): void {
        Route::get('contact', [ContactController::class, 'show'])->name('contact');
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
        Route::get('contact/token', [ContactController::class, 'token'])
            ->middleware(['throttle:contact-token', 'cache.headers:private;no_store'])
            ->name('contact.token');
    });
