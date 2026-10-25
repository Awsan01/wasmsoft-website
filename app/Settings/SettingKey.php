<?php

namespace App\Settings;

/**
 * Every site-wide setting editable in the dashboard.
 *
 * Localized settings store a locale => text map. Secrets and server
 * configuration never belong here: they stay in the environment.
 */
enum SettingKey: string
{
    case SiteName = 'site.name';
    case SiteTagline = 'site.tagline';
    case SiteDescription = 'site.description';
    case DefaultLocale = 'locales.default';
    case EnabledLocales = 'locales.enabled';
    case LogoMediaId = 'brand.logo_media_id';
    case FaviconMediaId = 'brand.favicon_media_id';
    case OgImageMediaId = 'seo.og_image_media_id';
    case ContactEmail = 'contact.email';
    case ContactPhone = 'contact.phone';
    case ContactAddress = 'contact.address';
    case ContactHours = 'contact.hours';
    case ContactMapUrl = 'contact.map_url';
    case InquiryNotificationEmail = 'contact.notification_email';
    case SocialLinks = 'social.links';
    case FooterNote = 'footer.note';

    public function isLocalized(): bool
    {
        return in_array($this, [
            self::SiteName,
            self::SiteTagline,
            self::SiteDescription,
            self::ContactAddress,
            self::ContactHours,
            self::FooterNote,
        ], true);
    }

    /**
     * Value used until an administrator saves the setting.
     */
    public function default(): mixed
    {
        return match ($this) {
            self::SiteName => array_fill_keys(array_keys(config('wasmsoft.locales')), 'Wasmsoft'),
            self::DefaultLocale => config('wasmsoft.default_locale'),
            self::EnabledLocales => array_keys(config('wasmsoft.locales')),
            self::SocialLinks => [],
            default => null,
        };
    }
}
