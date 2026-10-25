{{--
    Responsive image from the media library: WebP renditions in srcset,
    intrinsic width/height (no layout shift) and lazy loading unless
    `eager` (use for the first, above-the-fold image).
    `alt` falls back to the media item's own alternative text in the
    current language; pass alt="" for decorative images.
--}}
@props([
    'media',
    'alt' => null,
    'sizes' => '100vw',
    'eager' => false,
])

@php
    $alt ??= $media->altText() ?? '';
@endphp

<img
    src="{{ $media->urlForWidth(960) }}"
    @if ($media->srcset() !== '') srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" @endif
    @if ($media->width) width="{{ $media->width }}" height="{{ $media->height }}" @endif
    alt="{{ $alt }}"
    loading="{{ $eager ? 'eager' : 'lazy' }}"
    decoding="{{ $eager ? 'sync' : 'async' }}"
    @if ($eager) fetchpriority="high" @endif
    {{ $attributes }}
>
