<?php

namespace App\Http\Middleware;

use App\Localization\ClientTranslations;
use App\Localization\Locales;
use App\Navigation\NavLink;
use App\Navigation\SiteNavigation;
use App\Settings\ContactDetails;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\OnceProp;
use Inertia\Support\Header;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inertia for the public site: the root view (resources/views/app/site.blade.php)
 * and the data every page shares — language, brand, menus, footer and the
 * interface strings.
 *
 * Public pages run without a session, so nothing here reads or writes one
 * (validation errors are shared only on the session-backed contact routes).
 * Runs after `site.locale`, which has set the page language.
 */
class HandleSiteInertiaRequests extends Middleware
{
    /** Translation groups the public pages use in the browser. */
    public const TRANSLATION_GROUPS = ['site', 'ui'];

    protected $rootView = 'app.site';

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SiteSettings $settings,
        private readonly SiteNavigation $navigation,
        private readonly ClientTranslations $translations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn (): array => $this->locale(),
            'site' => fn (): array => $this->site(),
            'navigation' => fn (): array => $this->navigation(),
            'footer' => fn (): array => $this->footer(),
        ];
    }

    /**
     * Sent with the first page of a visit and kept by the browser while it
     * stays in this language (the key changes with the language and the strings).
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
     * Shared caches must keep apart the HTML page, the full page data and
     * the partial page data answers to Inertia's requests for one address.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        $response->setVary(array_values(array_unique([
            ...$response->getVary(),
            Header::INERTIA,
            Header::PARTIAL_COMPONENT,
            Header::PARTIAL_ONLY,
            Header::PARTIAL_EXCEPT,
            Header::EXCEPT_ONCE_PROPS,
        ])));

        return $response;
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
     * @return array{name: string, logoUrl: string, homeUrl: string, urls: array{services: string, projects: string, news: string, contact: string}}
     */
    private function site(): array
    {
        return [
            'name' => $this->brand->name(),
            'logoUrl' => $this->brand->logoUrl(),
            'homeUrl' => route('site.home'),
            'urls' => [
                'services' => route('site.services.index'),
                'projects' => route('site.projects.index'),
                'news' => route('site.news.index'),
                'contact' => route('site.contact'),
            ],
        ];
    }

    /**
     * @return array{header: list<array<string, mixed>>, panels: array<string, mixed>}
     */
    private function navigation(): array
    {
        $locale = app()->getLocale();
        $links = $this->navigation->header($locale);

        return [
            'header' => array_map(fn (NavLink $link): array => $link->toArray(), $links),
            'panels' => $this->navigation->panels($links, $locale),
        ];
    }

    /**
     * @return array{groups: list<array<string, mixed>>, description: string|null, note: string|null, contact: array<string, mixed>, year: int}
     */
    private function footer(): array
    {
        $locale = app()->getLocale();

        return [
            'groups' => array_map(fn (NavLink $group): array => $group->toArray(), $this->navigation->footer($locale)),
            'description' => $this->settings->localized(SettingKey::SiteDescription, $locale),
            'note' => $this->settings->localized(SettingKey::FooterNote, $locale),
            'contact' => ContactDetails::from($this->settings, $locale)->toArray(),
            'year' => (int) now()->year,
        ];
    }
}
