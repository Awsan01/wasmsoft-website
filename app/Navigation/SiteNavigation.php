<?php

namespace App\Navigation;

use App\Content\PageTemplate;
use App\Models\Page;

/**
 * Builds the public header and footer menus for a locale.
 */
class SiteNavigation
{
    /** @var array<string, ?NavLink> About-page link per locale, resolved once per request. */
    private array $aboutLinks = [];

    /**
     * @return list<NavLink>
     */
    public function header(string $locale): array
    {
        return array_values(array_filter([
            $this->aboutLink($locale),
            $this->route('site.services.index', __('site.nav.services', [], $locale), $locale, 'site.services.*'),
            $this->route('site.projects.index', __('site.nav.projects', [], $locale), $locale, 'site.projects.*'),
            $this->route('site.news.index', __('site.nav.news', [], $locale), $locale, 'site.news.*'),
        ]));
    }

    /**
     * Footer columns: each NavLink is a group heading with children.
     *
     * @return list<NavLink>
     */
    public function footer(string $locale): array
    {
        return [
            new NavLink(__('site.footer.company', [], $locale), null, children: array_values(array_filter([
                $this->aboutLink($locale),
                $this->route('site.news.index', __('site.nav.news', [], $locale), $locale, 'site.news.*'),
                $this->route('site.contact', __('site.nav.contact', [], $locale), $locale, 'site.contact'),
            ]))),
            new NavLink(__('site.footer.work', [], $locale), null, children: [
                $this->route('site.services.index', __('site.nav.services', [], $locale), $locale, 'site.services.*'),
                $this->route('site.projects.index', __('site.nav.projects', [], $locale), $locale, 'site.projects.*'),
            ]),
        ];
    }

    private function route(string $name, string $label, string $locale, string $activePattern): NavLink
    {
        return new NavLink($label, route($name, ['locale' => $locale]), request()->routeIs($activePattern));
    }

    /**
     * Link to the published About page in $locale, if it exists in that language.
     */
    private function aboutLink(string $locale): ?NavLink
    {
        if (! array_key_exists($locale, $this->aboutLinks)) {
            $page = Page::query()
                ->custom()
                ->published()
                ->where('template', PageTemplate::About)
                ->translatedIn($locale)
                ->withTranslation($locale)
                ->oldest('id')
                ->first();

            $url = $page?->publicUrl($locale);

            $this->aboutLinks[$locale] = $url === null ? null : new NavLink(__('site.nav.about', [], $locale), $url, request()->url() === $url);
        }

        return $this->aboutLinks[$locale];
    }
}
