<?php

namespace App\Support;

use App\Localization\Locales;
use App\Models\Media;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;

/**
 * Resolves the brand name and assets (logo, favicon, social image) used
 * across the site, the dashboard and outgoing email. Without images chosen
 * in the settings, the bundled, unmodified logo supplied by Wasmsoft and
 * the bundled icons are used. Scoped per request (see AppServiceProvider).
 */
class Brand
{
    /** @var array<string, Media|null> */
    private array $media = [];

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
        return $this->image(SettingKey::LogoMediaId)?->urlForWidth(480) ?? asset('images/brand/wasmsoft-logo.jpg');
    }

    /**
     * The favicon chosen in the settings, or null for the bundled icons.
     */
    public function faviconUrl(): ?string
    {
        return $this->image(SettingKey::FaviconMediaId)?->url();
    }

    /**
     * Image shared on social networks for pages without their own image.
     */
    public function socialImageUrl(): string
    {
        return $this->image(SettingKey::OgImageMediaId)?->urlForWidth(1200) ?? asset('images/brand/wasmsoft-logo.jpg');
    }

    private function image(SettingKey $key): ?Media
    {
        if (! array_key_exists($key->value, $this->media)) {
            $id = $this->settings->get($key);

            $this->media[$key->value] = is_numeric($id) ? Media::query()->images()->find((int) $id) : null;
        }

        return $this->media[$key->value];
    }
}
