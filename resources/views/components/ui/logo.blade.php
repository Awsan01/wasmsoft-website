{{--
    Brand lock-up: the supplied Wasmsoft logo (used unaltered) followed by the
    company name. The image is decorative because the name is rendered as
    text next to it. `src` lets the dashboard-configured logo replace the
    bundled default.
--}}
@props([
    'href' => null,
    'name' => 'Wasmsoft',
    'src' => null,
    'size' => 40,
    'tone' => 'default',
    'showName' => true,
])

@php
    $src ??= asset('images/brand/wasmsoft-logo.jpg');
    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['group inline-flex shrink-0 items-center gap-3 rounded-md']) }}
>
    <img
        src="{{ $src }}"
        width="{{ $size }}"
        height="{{ $size }}"
        alt="{{ $showName ? '' : $name }}"
        decoding="async"
        @class(['shrink-0 rounded-sm object-cover', 'ring-1 ring-white/15' => $tone === 'dark'])
    >
    @if ($showName)
        <span @class([
            'text-xl leading-none font-extrabold',
            'text-primary' => $tone !== 'dark',
            'text-white' => $tone === 'dark',
        ])>{{ $name }}</span>
    @endif
</{{ $tag }}>
