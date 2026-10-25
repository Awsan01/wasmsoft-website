<?php

namespace App\View\Components\Admin;

use App\Localization\Locales;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Dashboard top bar: drawer toggle (small screens), breadcrumbs, link to
 * the public site, dashboard language and the account menu.
 */
class Topbar extends Component
{
    /**
     * @param  list<array{label: string, url: ?string}>  $breadcrumbs
     */
    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        public array $breadcrumbs = [],
    ) {}

    public function render(): View
    {
        $locale = app()->getLocale();

        return view('components.admin.topbar', [
            'user' => request()->user(),
            'siteName' => $this->brand->name(),
            'logoUrl' => $this->brand->logoUrl(),
            // The public site in the dashboard language when it is published, otherwise the default.
            'siteUrl' => route('site.home', ['locale' => $this->locales->isEnabled($locale) ? $locale : $this->locales->default()]),
        ]);
    }
}
