<?php

namespace App\Navigation;

/**
 * Builds the public header and footer menus for a locale.
 */
class SiteNavigation
{
    /**
     * @return list<NavLink>
     */
    public function header(string $locale): array
    {
        return [
            $this->route('site.about', __('site.nav.about', [], $locale), $locale, 'site.about'),
            $this->route('site.services.index', __('site.nav.services', [], $locale), $locale, 'site.services.*'),
            $this->route('site.projects.index', __('site.nav.projects', [], $locale), $locale, 'site.projects.*'),
            $this->route('site.news.index', __('site.nav.news', [], $locale), $locale, 'site.news.*'),
        ];
    }

    /**
     * Footer columns: each NavLink is a group heading with children.
     *
     * @return list<NavLink>
     */
    public function footer(string $locale): array
    {
        return [
            new NavLink(__('site.footer.company', [], $locale), null, children: [
                $this->route('site.about', __('site.nav.about', [], $locale), $locale, 'site.about'),
                $this->route('site.news.index', __('site.nav.news', [], $locale), $locale, 'site.news.*'),
                $this->route('site.contact', __('site.nav.contact', [], $locale), $locale, 'site.contact'),
            ]),
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
}
