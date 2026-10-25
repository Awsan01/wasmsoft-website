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
});
