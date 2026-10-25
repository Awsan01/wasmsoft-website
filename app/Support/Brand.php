<?php

namespace App\Support;

use App\Localization\Locales;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;

/**
 * Resolves the brand name and assets (logo, social image) used across the
 * site, the dashboard and outgoing email. Defaults to the bundled,
 * unmodified logo supplied by Wasmsoft.
 */
class Brand
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly Locales $locales,
    ) {}

    /**
     * The company name in the given locale (the current one by default).
     * A brand name may fall back to the default locale's spelling.
     */
    public function name(?string $locale = null): string
    {
        return $this->settings->localized(SettingKey::SiteName, $locale ?? app()->getLocale(), $this->locales->default())
            ?? (string) config('app.name');
    }

    public function logoUrl(): string
    {
        return asset('images/brand/wasmsoft-logo.jpg');
    }
}
