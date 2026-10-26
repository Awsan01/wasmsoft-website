{{-- Favicon links: the icon chosen in the settings, otherwise the bundled set. --}}
@php
    $favicon = app(\App\Support\Brand::class)->faviconUrl();
@endphp

@if ($favicon)
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endif
