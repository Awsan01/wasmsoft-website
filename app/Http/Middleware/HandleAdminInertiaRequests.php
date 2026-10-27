<?php

namespace App\Http\Middleware;

use App\Localization\ClientTranslations;
use App\Localization\Formatter;
use App\Localization\Locales;
use App\Models\Media;
use App\Navigation\AdminLink;
use App\Navigation\AdminNavigation;
use App\Support\Brand;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\OnceProp;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inertia for the dashboard: the root view (resources/views/app/admin.blade.php)
 * and what every dashboard page shares — the dashboard language, the brand,
 * the signed-in account, the sidebar and the interface texts.
 *
 * The one-time messages that dashboard redirects set (`status`, `warning`,
 * `error`, see Admin controllers) are passed on as Inertia flash data: shown
 * once, and never brought back by the browser's back button. Over HTTPS the
 * page data kept in the browser history is encrypted, and signing out
 * clears it (the browser's crypto API needs a secure connection).
 *
 * Runs after `admin.locale`, which has set the dashboard language.
 */
class HandleAdminInertiaRequests extends Middleware
{
    /** Translation groups the dashboard uses in the browser. */
    public const TRANSLATION_GROUPS = ['admin', 'ui', 'site.errors'];

    /** Session keys of the messages set by dashboard redirects. */
    private const FLASH_KEYS = ['status', 'warning', 'error'];

    protected $rootView = 'app.admin';

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly AdminNavigation $navigation,
        private readonly Formatter $formatter,
        private readonly ClientTranslations $translations,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isSecure()) {
            Inertia::encryptHistory();
        }

        // Answers to the media picker (JSON) leave the messages to the next page.
        if ($request->hasSession() && ! $request->expectsJson()) {
            foreach (self::FLASH_KEYS as $key) {
                $message = $request->session()->get($key);

                if (is_string($message) && $message !== '') {
                    Inertia::flash($key, $message);
                }
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn (): array => $this->locale(),
            'admin' => fn (): array => $this->admin($request),
            'auth' => fn (): ?array => $this->auth($request),
            'navigation' => fn (): array => $this->navigation($request),
        ];
    }

    /**
     * Sent with the first page and kept by the browser while the dashboard
     * language and its texts stay the same.
     *
     * @return array<string, OnceProp>
     */
    public function shareOnce(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'translations' => Inertia::once(fn (): array => $this->translations->messages($locale, self::TRANSLATION_GROUPS))
                ->as($this->translations->onceKey($locale, self::TRANSLATION_GROUPS)),
        ];
    }

    /**
     * @return array{code: string, dir: string, intl: string}
     */
    private function locale(): array
    {
        $locale = app()->getLocale();

        return [
            'code' => $locale,
            'dir' => $this->locales->direction($locale),
            'intl' => $this->locales->intl($locale),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function admin(Request $request): array
    {
        $locale = app()->getLocale();
        $user = $request->user();

        return [
            'name' => $this->brand->name(),
            'logoUrl' => $this->brand->logoUrl(),
            'dashboardUrl' => route('admin.dashboard'),
            // The public site in the dashboard language when it is published there, otherwise in the default language.
            'siteUrl' => route('site.home', ['locale' => $this->locales->isEnabled($locale) ? $locale : $this->locales->default()]),
            'localeUrl' => route('admin.locale'),
            'locales' => array_map(fn (string $code): array => [
                'code' => $code,
                'name' => $this->locales->nativeName($code),
                'dir' => $this->locales->direction($code),
            ], $this->locales->supported()),
            'defaultLocale' => $this->locales->default(),
            'media' => [
                'pickerUrl' => route('admin.media.picker'),
                'uploadUrl' => route('admin.media.store'),
                'canUpload' => $user?->can('create', Media::class) ?? false,
            ],
        ];
    }

    /**
     * @return array{user: array{id: int, name: string, email: string, initial: string, role: string}, logoutUrl: string, profileUrl: string}|null
     */
    private function auth(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return [
            'user' => [
                'id' => (int) $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'initial' => Str::upper(Str::substr($user->name, 0, 1)),
                'role' => $user->role->label(),
            ],
            'logoutUrl' => route('admin.logout'),
            'profileUrl' => route('admin.profile.edit'),
        ];
    }

    /**
     * @return list<array{key: string, label: string|null, links: list<array{url: string, label: string, icon: string, active: bool, badge: array{count: int, label: string}|null}>}>
     */
    private function navigation(Request $request): array
    {
        return array_map(fn (array $group): array => [
            'key' => $group['key'],
            'label' => $group['label'],
            'links' => array_map(fn (AdminLink $link): array => [
                'url' => $link->url,
                'label' => $link->label,
                'icon' => $link->icon,
                'active' => $link->active,
                'badge' => $link->badge ? ['count' => $link->badge, 'label' => $this->formatter->number($link->badge)] : null,
            ], $group['links']),
        ], $this->navigation->groups($request->user()));
    }
}
