{{--
    Error pages. Self-contained on purpose: no database access (settings,
    navigation, brand images), so they still render when the database is
    down or the site is in maintenance. The language comes from the URL,
    the current request or the browser; nothing technical is shown.
--}}
@php
    $locales = app(\App\Localization\Locales::class);
    $segment = request()->segment(1);
    $locale = match (true) {
        $locales->isSupported($segment) => $segment,
        request()->route() !== null && $locales->isSupported(app()->getLocale()) => app()->getLocale(),
        default => $locales->negotiate(request(), $locales->supported()),
    };
    app()->setLocale($locale);

    $inDashboard = $segment === 'admin';
    $home = $inDashboard ? url('/admin') : ($locales->isSupported($segment) ? url('/'.$segment) : url('/'));

    // Translated here, after the language is known (status views only pass their code).
    $title = __("site.errors.{$code}.title");
    $message = __("site.errors.{$code}.message");
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locales->direction($locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    <x-ui.fonts :aliases="$locale === 'ar' ? null : ['jakarta']" />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-dvh bg-lavender">
    <div class="relative isolate flex min-h-dvh flex-col">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true"></div>

        <header class="container-page py-6">
            <x-ui.logo :href="$home" :name="config('app.name')" />
        </header>

        <main id="main" class="container-page flex flex-1 items-center pb-16">
            <div class="grid max-w-xl gap-5">
                <p class="flex items-center gap-2.5 text-eyebrow text-secondary">
                    <span class="slash-mark" aria-hidden="true"></span>
                    {{ __('site.errors.code', ['code' => $code]) }}
                </p>
                <h1 class="text-h1">{{ $title }}</h1>
                <p class="text-lead text-muted">{{ $message }}</p>
                <div class="pt-2">
                    <x-ui.button :href="$home" :icon="$inDashboard ? 'layout-dashboard' : 'home'">
                        {{ $inDashboard ? __('site.errors.dashboard') : __('site.errors.home') }}
                    </x-ui.button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
