<?php

/*
|--------------------------------------------------------------------------
| Public site routes (session-less "site" middleware group)
|--------------------------------------------------------------------------
|
| Every page lives under a locale prefix (/en, /ar, /tr). Routes that need a
| session — the contact form — are registered in routes/web.php instead.
| The custom page catch-all must stay last.
|
*/

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LocaleRedirectController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\SitemapController;
use App\Localization\Locales;
use Illuminate\Support\Facades\Route;

Route::get('/', LocaleRedirectController::class)->name('site.root');
Route::get('sitemap.xml', SitemapController::class)->name('site.sitemap');
Route::get('robots.txt', RobotsController::class)->name('site.robots');

Route::prefix('{locale}')
    ->where(['locale' => app(Locales::class)->routePattern()])
    ->middleware(['site.locale', 'site.cache'])
    ->name('site.')
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');

        if (app()->isLocal()) {
            Route::view('_styleguide', 'site.styleguide')->name('styleguide');
        }

        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('services/{slug}', [ServiceController::class, 'show'])->name('services.show');

        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('news', [PostController::class, 'index'])->name('news.index');
        Route::get('news/category/{slug}', [PostController::class, 'category'])->name('news.category');
        Route::get('news/tag/{slug}', [PostController::class, 'tag'])->name('news.tag');
        Route::get('news/{slug}', [PostController::class, 'show'])->name('news.show');

        Route::get('{slug}', [PageController::class, 'show'])->name('pages.show');
    });
