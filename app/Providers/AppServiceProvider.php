<?php

namespace App\Providers;

use App\Auth\Permission;
use App\Localization\Formatter;
use App\Localization\Locales;
use App\Models\Media;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use App\Navigation\AdminNavigation;
use App\Navigation\SiteNavigation;
use App\Security\InlineStyles;
use App\Settings\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Scoped (per request) so long-running workers never serve stale settings.
        $this->app->scoped(SiteSettings::class);
        $this->app->scoped(Locales::class);
        $this->app->scoped(Formatter::class);
        $this->app->scoped(AdminNavigation::class);
        $this->app->scoped(SiteNavigation::class);
        $this->app->scoped(InlineStyles::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Outside production, fail loudly on lazy loading (N+1 queries),
        // unfillable attributes and missing attributes.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Store short aliases instead of class names in polymorphic columns
        // (audit log subjects); unmapped models fail loudly.
        Relation::enforceMorphMap([
            'user' => User::class,
            'page' => Page::class,
            'media' => Media::class,
            'service' => Service::class,
        ]);

        // Refuse migrate:fresh, db:wipe and similar commands in production.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        Password::defaults(function () {
            $rule = Password::min(12)->letters()->mixedCase()->numbers();

            // Checks the Have I Been Pwned range API (k-anonymity); network access required.
            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.simple');

        // Vite remembers which assets a response already preloaded. Reset that
        // after every request so long-lived workers (and tests) render each
        // response identically, which keeps public-page ETags stable.
        Event::listen(RequestHandled::class, fn () => Vite::flush());

        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    /**
     * Each permission becomes a gate ability, e.g. `can:content.manage`.
     * Model policies (app/Policies) add ownership and state rules on top.
     */
    private function configureAuthorization(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user): bool => $user->hasPermission($permission));
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(20)->by('login|'.$request->ip()));

        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)->by('reset|'.$request->ip()));

        RateLimiter::for('contact', fn (Request $request) => Limit::perHour(
            (int) config('wasmsoft.contact.max_per_hour'),
        )->by('contact|'.$request->ip()));

        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(30)->by('uploads|'.$request->user()?->getAuthIdentifier()));

        RateLimiter::for('dashboard', fn (Request $request) => Limit::perMinute(300)->by('dashboard|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
