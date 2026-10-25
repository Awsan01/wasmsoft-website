<?php

/*
|--------------------------------------------------------------------------
| Session-backed routes ("web" middleware group: cookies, session, CSRF)
|--------------------------------------------------------------------------
|
| The public contact form (it needs CSRF protection and validation
| feedback) and, in later steps, authentication and the dashboard.
|
*/

use App\Http\Controllers\Site\ContactController;
use App\Localization\Locales;
use Illuminate\Support\Facades\Route;

Route::prefix('{locale}')
    ->where(['locale' => app(Locales::class)->routePattern()])
    ->middleware('site.locale')
    ->name('site.')
    ->group(function (): void {
        Route::get('contact', [ContactController::class, 'show'])->name('contact');
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
    });
