{{--
    Root view of the dashboard (an Inertia app, see resources/js/admin.tsx).
    The page title arrives as the page's `head` prop; every font is loaded,
    as editors work on content in all languages whatever the dashboard language.
--}}
@php
    $props = $page['props'];
@endphp
<!DOCTYPE html>
<html lang="{{ $props['locale']['code'] ?? app()->getLocale() }}" dir="{{ $props['locale']['dir'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    @foreach ($props['head'] ?? [] as $element)
        {!! $element !!}
    @endforeach
    <x-ui.favicons />
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    <x-ui.fonts />
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/admin.tsx', "resources/js/pages/{$page['component']}.tsx"])
</head>
<body class="min-h-dvh bg-canvas">
    @inertia
</body>
</html>
