{{-- Document metadata for search engines and social previews. --}}
@props(['seo'])

@php
    $locales = app(\App\Localization\Locales::class);
    $locale = app()->getLocale();
    $siteName = app(\App\Support\Brand::class)->name($locale);
    $defaultLocale = $locales->default();
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    // Pages without their own image are shared with the site-wide image.
    $image = $seo->image ?? app(\App\Support\Brand::class)->socialImageUrl();
@endphp

<title>{{ $seo->title }}</title>
@if ($seo->description)
    <meta name="description" content="{{ $seo->description }}">
@endif
@unless ($seo->indexable)
    <meta name="robots" content="noindex, nofollow">
@endunless
@if ($seo->canonical)
    <link rel="canonical" href="{{ $seo->canonical }}">
@endif

@if (count($seo->alternates) > 1)
    @foreach ($seo->alternates as $code => $url)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $url }}">
    @endforeach
    @isset($seo->alternates[$defaultLocale])
        <link rel="alternate" hreflang="x-default" href="{{ $seo->alternates[$defaultLocale] }}">
    @endisset
@endif

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:title" content="{{ $seo->title }}">
@if ($seo->description)
    <meta property="og:description" content="{{ $seo->description }}">
@endif
@if ($seo->canonical)
    <meta property="og:url" content="{{ $seo->canonical }}">
@endif
<meta property="og:locale" content="{{ $locales->openGraph($locale) }}">
@foreach (array_keys($seo->alternates) as $code)
    @if ($code !== $locale)
        <meta property="og:locale:alternate" content="{{ $locales->openGraph($code) }}">
    @endif
@endforeach
<meta property="og:image" content="{{ $image }}">
@if ($seo->image && $seo->imageAlt)
    <meta property="og:image:alt" content="{{ $seo->imageAlt }}">
@endif
<meta name="twitter:card" content="{{ $seo->image ? 'summary_large_image' : 'summary' }}">
@if ($seo->type === 'article')
    @if ($seo->publishedAt)
        <meta property="article:published_time" content="{{ $seo->publishedAt->toAtomString() }}">
    @endif
    @if ($seo->modifiedAt)
        <meta property="article:modified_time" content="{{ $seo->modifiedAt->toAtomString() }}">
    @endif
@endif

{{-- JSON-LD is a data block, not script: the CSP does not apply and no nonce is needed. --}}
@foreach ($seo->structuredData as $data)
    <script type="application/ld+json">{!! json_encode($data, $jsonFlags) !!}</script>
@endforeach
