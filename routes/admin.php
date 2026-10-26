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

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\InvitationController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::post('locale', LocaleController::class)->name('locale');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:password-reset')->name('password.store');

    Route::get('invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('invitation', [InvitationController::class, 'store'])->middleware('throttle:password-reset')->name('invitation.store');
});

Route::middleware(['auth', 'auth.session', 'admin.active', 'can:dashboard.view', 'throttle:dashboard'])->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('pages', PageController::class)->except('show');
    Route::get('pages/{page}/preview/{locale}', [PageController::class, 'preview'])->name('pages.preview');

    Route::resource('services', ServiceController::class)->except('show');
    Route::patch('services/{service}/move', [ServiceController::class, 'move'])->name('services.move');
    Route::get('services/{service}/preview/{locale}', [ServiceController::class, 'preview'])->name('services.preview');

    Route::resource('projects', ProjectController::class)->except('show');
    Route::get('projects/{project}/preview/{locale}', [ProjectController::class, 'preview'])->name('projects.preview');
    Route::resource('project-categories', ProjectCategoryController::class)->except('show')->parameters(['project-categories' => 'term']);
    Route::patch('project-categories/{term}/move', [ProjectCategoryController::class, 'move'])->name('project-categories.move');

    Route::resource('posts', PostController::class)->except('show');
    Route::get('posts/{post}/preview/{locale}', [PostController::class, 'preview'])->name('posts.preview');
    Route::resource('post-categories', PostCategoryController::class)->except('show')->parameters(['post-categories' => 'term']);
    Route::patch('post-categories/{term}/move', [PostCategoryController::class, 'move'])->name('post-categories.move');
    Route::resource('tags', TagController::class)->except('show')->parameters(['tags' => 'term']);

    Route::resource('inquiries', InquiryController::class)->only(['index', 'show', 'update', 'destroy']);

    // Media library ("media" is used as-is: a resource route would singularize it to "medium").
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [MediaController::class, 'picker'])->name('media.picker');
    Route::post('media', [MediaController::class, 'store'])->middleware('throttle:uploads')->name('media.store');
    Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
    Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');

    Route::resource('users', UserController::class)->except('show');
    Route::post('users/{user}/invitation', [UserController::class, 'invite'])->name('users.invite');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->middleware('throttle:login')->name('profile.password');
});
