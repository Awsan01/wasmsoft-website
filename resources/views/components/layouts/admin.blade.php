{{--
    Dashboard layout: sidebar (static on large screens, a modal drawer on
    small ones), top bar, flash messages and the shared confirmation
    dialog. Pages pass a `title` and optional `breadcrumbs`.
--}}
@props([
    'title',
    'breadcrumbs' => [],
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
    <meta name="robots" content="noindex, nofollow">
    {{-- Read by scripts that post with fetch() (media uploads from the picker). --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ __('admin.meta_title', ['site' => app(\App\Support\Brand::class)->name()]) }}</title>
    <x-ui.favicons />
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    {{-- Every font: editors work on content in all languages, whatever the dashboard language. --}}
    <x-ui.fonts />
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh bg-canvas">
    <a href="#main" class="skip-link">{{ __('ui.skip_to_content') }}</a>

    <div class="lg:grid lg:min-h-dvh lg:grid-cols-[var(--spacing-sidebar)_minmax(0,1fr)]">
        <aside class="sticky top-0 hidden h-dvh border-e border-border bg-surface lg:block">
            <x-admin.sidebar />
        </aside>

        <dialog id="admin-drawer" aria-label="{{ __('admin.nav.label') }}" class="admin-drawer lg:hidden">
            <x-admin.sidebar drawer />
        </dialog>

        <div class="flex min-h-dvh min-w-0 flex-col">
            <x-admin.topbar :breadcrumbs="$breadcrumbs" />

            <main id="main" tabindex="-1" class="flex-1 px-gutter py-8 focus:outline-none">
                <div class="mx-auto grid w-full max-w-admin gap-6">
                    <x-admin.flash />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-ui.confirm-dialog />
    @stack('dialogs')
</body>
</html>
