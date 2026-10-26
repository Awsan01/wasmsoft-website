{{-- Sign-in, password reset and invitation pages for the dashboard. --}}
@props(['title'])

@php
    $locales = app(\App\Localization\Locales::class);
    $locale = app()->getLocale();
    $brand = app(\App\Support\Brand::class);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locales->direction($locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · {{ __('admin.meta_title', ['site' => $brand->name()]) }}</title>
    <x-ui.favicons />
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    <x-ui.fonts :aliases="$locale === 'ar' ? null : ['jakarta']" />
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh bg-lavender">
    <div class="relative isolate flex min-h-dvh flex-col">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true"></div>

        <header class="container-page flex items-center justify-between gap-4 py-6">
            <x-ui.logo :name="$brand->name()" :src="$brand->logoUrl()" />
            <x-admin.locale-switcher />
        </header>

        <main id="main" class="flex flex-1 items-start justify-center px-gutter pt-6 pb-16 sm:items-center sm:pt-0">
            <div class="w-full max-w-md">
                <x-ui.card padding="lg" class="shadow-md">
                    {{ $slot }}
                </x-ui.card>
            </div>
        </main>
    </div>
</body>
</html>
