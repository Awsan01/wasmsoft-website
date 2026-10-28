{{--
    Button / link-button.
    Renders an <a> when `href` is given, otherwise a <button>.
    Variants: primary, secondary, outline, ghost, danger, on-dark, on-dark-outline.
    `pill` gives it the fully round ends of the public site's buttons.
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconEnd' => null,
    'block' => false,
    'pill' => false,
])

@php
    $sizes = $pill ? [
        'sm' => 'h-control-sm gap-1.5 px-4 text-sm',
        'md' => 'h-control-md gap-2 px-6 text-sm',
        'lg' => 'h-control-lg gap-2.5 px-8 text-base',
    ] : [
        'sm' => 'h-control-sm gap-1.5 px-3.5 text-sm',
        'md' => 'h-control-md gap-2 px-5 text-sm',
        'lg' => 'h-control-lg gap-2.5 px-7 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary text-white shadow-sm hover:bg-primary-hover',
        'secondary' => 'bg-lavender text-primary hover:bg-lavender-strong',
        'outline' => 'border border-border-strong bg-surface text-ink hover:border-primary hover:text-primary',
        'ghost' => 'text-ink hover:bg-lavender hover:text-primary',
        'danger' => 'bg-error text-white shadow-sm hover:bg-error-hover',
        'on-dark' => 'bg-white text-primary shadow-sm hover:bg-lavender',
        'on-dark-outline' => 'border border-white/45 text-white hover:border-white hover:bg-white/10',
    ];

    $iconSize = ['sm' => 16, 'md' => 18, 'lg' => 20][$size] ?? 18;

    $classes = implode(' ', [
        'inline-flex shrink-0 items-center justify-center font-semibold whitespace-nowrap select-none transition-base',
        $pill ? 'rounded-full' : 'rounded-md',
        'active:translate-y-px disabled:pointer-events-none disabled:opacity-55 aria-disabled:pointer-events-none aria-disabled:opacity-55',
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
        $block ? 'w-full' : '',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        <span>{{ $slot }}</span>
        @if ($iconEnd)<x-ui.icon :name="$iconEnd" :size="$iconSize" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$iconSize" />@endif
        <span>{{ $slot }}</span>
        @if ($iconEnd)<x-ui.icon :name="$iconEnd" :size="$iconSize" />@endif
    </button>
@endif
