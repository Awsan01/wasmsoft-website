{{--
    Public site layout.

    `seo` (App\Seo\SeoMeta) is required: every public page declares its own
    title, description, canonical URL and the locales it exists in.
    `languageUrls` optionally overrides where the language switcher points
    for locales in which this page has no translation (defaults to that
    locale's home page). `preview` marks a dashboard preview of unpublished
    content with a banner.
--}}
@props([
    'seo',
    'languageUrls' => [],
    'preview' => false,
])

@php
    $locales = app(\App\Localization\Locales::class);
    $locale = app()->getLocale();
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locales->direction($locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site.seo :seo="$seo" />
    <x-ui.favicons />
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    {{-- The Arabic font is only loaded where Arabic is the page language. --}}
    <x-ui.fonts :aliases="$locale === 'ar' ? null : ['jakarta']" />
    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="relative flex min-h-dvh flex-col">
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-px" aria-hidden="true"></div>
    <a href="#main" class="skip-link">{{ __('ui.skip_to_content') }}</a>

    @if ($preview)
        <div role="status" class="border-b border-warning-line bg-warning-soft text-warning-ink">
            <p class="container-page flex items-center gap-2 py-2.5 text-sm font-semibold">
                <x-ui.icon name="eye" :size="18" />
                {{ __('site.preview.banner') }}
            </p>
        </div>
    @endif

    <x-site.header :alternates="$seo->alternates" :language-urls="$languageUrls" />

    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>

    <x-site.footer />
</body>
</html>
