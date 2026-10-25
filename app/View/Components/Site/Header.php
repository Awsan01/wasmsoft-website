<?php

namespace App\View\Components\Site;

use App\Localization\Locales;
use App\Navigation\SiteNavigation;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    /**
     * @param  array<string, string>  $alternates  locale => URL of this page's translations
     * @param  array<string, string>  $languageUrls  locale => fallback URL when no translation exists
     */
    public function __construct(
        private readonly SiteNavigation $navigation,
        private readonly Locales $locales,
        private readonly Brand $brand,
        public array $alternates = [],
        public array $languageUrls = [],
    ) {}

    public function render(): View
    {
        $locale = app()->getLocale();

        return view('components.site.header', [
            'links' => $this->navigation->header($locale),
            'siteName' => $this->brand->name($locale),
            'logoUrl' => $this->brand->logoUrl(),
            'languages' => $this->languages($locale),
        ]);
    }

    /**
     * @return list<array{code: string, name: string, dir: string, url: string, current: bool}>
     */
    private function languages(string $current): array
    {
        return array_map(fn (string $code): array => [
            'code' => $code,
            'name' => $this->locales->nativeName($code),
            'dir' => $this->locales->direction($code),
            'url' => $this->alternates[$code] ?? $this->languageUrls[$code] ?? route('site.home', ['locale' => $code]),
            'current' => $code === $current,
        ], $this->locales->enabled());
    }
}
