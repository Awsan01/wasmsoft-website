<?php

namespace App\View\Components\Site;

use App\Navigation\SiteNavigation;
use App\Settings\ContactDetails;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Footer extends Component
{
    public function __construct(
        private readonly SiteNavigation $navigation,
        private readonly SiteSettings $settings,
        private readonly Brand $brand,
    ) {}

    public function render(): View
    {
        $locale = app()->getLocale();

        return view('components.site.footer', [
            'groups' => $this->navigation->footer($locale),
            'siteName' => $this->brand->name($locale),
            'description' => $this->settings->localized(SettingKey::SiteDescription, $locale),
            'logoUrl' => $this->brand->logoUrl(),
            'contact' => ContactDetails::from($this->settings, $locale),
            'note' => $this->settings->localized(SettingKey::FooterNote, $locale),
        ]);
    }
}
