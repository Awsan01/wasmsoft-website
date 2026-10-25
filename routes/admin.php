<?php

/*
|--------------------------------------------------------------------------
| Dashboard routes (prefix /admin, route names admin.*)
|--------------------------------------------------------------------------
|
| Registered in bootstrap/app.php with the "web" group (cookies, session,
| CSRF), the dashboard locale and no-store cache headers. Everything except
| the sign-in pages requires an active account with dashboard access; each
| module adds its own permission middleware and model policies.
|
*/

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ServiceController;
use Illuminate\Support\Facades\Route;

Route::post('locale', LocaleController::class)->name('locale');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:password-reset')->name('password.store');
});

Route::middleware(['auth', 'auth.session', 'admin.active', 'can:dashboard.view', 'throttle:dashboard'])->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('pages', PageController::class)->except('show');
    Route::get('pages/{page}/preview/{locale}', [PageController::class, 'preview'])->name('pages.preview');

    Route::resource('services', ServiceController::class)->except('show');
    Route::patch('services/{service}/move', [ServiceController::class, 'move'])->name('services.move');
    Route::get('services/{service}/preview/{locale}', [ServiceController::class, 'preview'])->name('services.preview');

    // Media library ("media" is used as-is: a resource route would singularize it to "medium").
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [MediaController::class, 'picker'])->name('media.picker');
    Route::post('media', [MediaController::class, 'store'])->middleware('throttle:uploads')->name('media.store');
    Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
    Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});
