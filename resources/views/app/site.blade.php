{{--
    Root view of the public site (an Inertia app, see resources/js/site.tsx).

    The <head> elements every page needs for search engines and link
    previews (title, description, canonical and hreflang links, Open Graph,
    JSON-LD) arrive as the page's `head` prop and are printed here, so they
    never depend on JavaScript; the browser keeps them up to date on
    client-side visits. With server-side rendering on (config/inertia.php),
    the rendered head and body replace both.
--}}
@php
    $props = $page['props'];
    $locale = $props['locale']['code'] ?? app()->getLocale();
    $ssr = app(\Inertia\Ssr\SsrState::class)->setPage($page)->dispatch();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $props['locale']['dir'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($ssr)
        {!! $ssr->head !!}
    @else
        @foreach ($props['head'] ?? [] as $element)
            {!! $element !!}
        @endforeach
    @endif
    <x-ui.favicons />
    <meta name="theme-color" content="{{ config('wasmsoft.theme_color') }}">
    {{-- The Arabic font is only loaded where Arabic is the page language. --}}
    <x-ui.fonts :aliases="$locale === 'ar' ? null : ['jakarta']" />
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/site.tsx', "resources/js/pages/{$page['component']}.tsx"])
</head>
<body>
    @inertia
</body>
</html>
