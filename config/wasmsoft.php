<?php

/*
|--------------------------------------------------------------------------
| Wasmsoft application configuration
|--------------------------------------------------------------------------
|
| Business settings that editors change (company details, enabled locales,
| social links…) live in the database and are managed in the dashboard.
| This file holds the technical defaults and limits that should only change
| through a code review.
|
*/

return [

    /*
    | Supported locales, in the order they appear in language selectors.
    | `intl` is the ICU locale used for dates and numbers: Arabic uses Latin
    | (Western) digits by default ("-u-nu-latn"); remove that suffix to switch
    | to Arabic-Indic digits. Which locales are enabled — and which is the
    | default — is managed in the dashboard; these values are the fallback.
    */
    'locales' => [
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'intl' => 'ar-u-nu-latn', 'og' => 'ar_AR'],
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'intl' => 'en-GB', 'og' => 'en_GB'],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'dir' => 'ltr', 'intl' => 'tr-TR', 'og' => 'tr_TR'],
    ],

    'default_locale' => env('APP_LOCALE', 'en'),

    /*
    | Timezone used to show dates and to read dates typed in the dashboard
    | (e.g. scheduled publication). Timestamps are always stored in UTC.
    */
    'display_timezone' => env('DISPLAY_TIMEZONE', 'UTC'),

    // Browser UI colour (<meta name="theme-color">); matches --color-primary.
    'theme_color' => '#481467',

    'media' => [
        'disk' => env('MEDIA_DISK', 'public'),
        'max_image_kb' => (int) env('MEDIA_MAX_IMAGE_KB', 8192),
        'max_document_kb' => (int) env('MEDIA_MAX_DOCUMENT_KB', 15360),
        // Images larger than this are rejected before decoding (decompression bombs).
        'max_image_pixels' => 25_000_000,
        'max_image_edge' => 8000,
        // Responsive WebP renditions generated on upload (widths in pixels).
        'image_widths' => [480, 960, 1600],
        'webp_quality' => 82,
        'jpeg_quality' => 88,
    ],

    'contact' => [
        // Submissions faster than this (seconds after the form was rendered) are treated as bots.
        'min_seconds' => 3,
        // Forms older than this (minutes) must be reloaded.
        'max_age_minutes' => 120,
        'max_per_hour' => (int) env('CONTACT_MAX_PER_HOUR', 5),
        // Inquiries are permanently deleted after this many days (personal-data retention).
        'retention_days' => (int) env('INQUIRY_RETENTION_DAYS', 730),
    ],

    'audit' => [
        'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),
    ],

    'pagination' => [
        'site' => 9,
        'admin' => 20,
    ],

    // Lifetime of cached public content (seconds). Content changes invalidate the cache immediately.
    'cache_ttl' => (int) env('CONTENT_CACHE_TTL', 3600),

    // Shared-cache lifetime for public pages (Cache-Control s-maxage). 0 disables shared caching.
    'http_cache_seconds' => (int) env('HTTP_CACHE_SECONDS', 300),

    /*
    | Local development only (database/seeders/DevelopmentSeeder.php, which
    | refuses to run in other environments): password of the demo accounts.
    */
    'development' => [
        'seed_password' => env('DEV_SEED_PASSWORD', 'Wasmsoft-Dev-2026'),
    ],

];
