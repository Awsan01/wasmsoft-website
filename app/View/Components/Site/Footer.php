<?php

namespace App\View\Components\Site;

use App\Navigation\SiteNavigation;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use App\Support\SocialNetwork;
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
            'email' => $this->settings->get(SettingKey::ContactEmail),
            'phone' => $this->settings->get(SettingKey::ContactPhone),
            'address' => $this->settings->localized(SettingKey::ContactAddress, $locale),
            'socialLinks' => SocialNetwork::linksFrom($this->settings->get(SettingKey::SocialLinks)),
            'note' => $this->settings->localized(SettingKey::FooterNote, $locale),
        ]);
    }
}
