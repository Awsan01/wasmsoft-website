{{--
    Square icon-only button. `label` is required: it becomes the accessible
    name (icon-only controls must never be unlabeled).
--}}
@props([
    'icon',
    'label',
    'variant' => 'ghost',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $sizes = ['sm' => 'size-control-sm', 'md' => 'size-control-md', 'lg' => 'size-control-lg'];
    $variants = [
        'ghost' => 'text-muted hover:bg-lavender hover:text-primary',
        'outline' => 'border border-border bg-surface text-ink hover:border-primary hover:text-primary',
        'danger' => 'text-error hover:bg-error-soft',
        'on-dark' => 'text-white hover:bg-white/10',
    ];
    $classes = implode(' ', [
        'inline-grid shrink-0 place-items-center rounded-md transition-base disabled:pointer-events-none disabled:opacity-55',
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['ghost'],
    ]);
    $iconSize = ['sm' => 16, 'md' => 20, 'lg' => 22][$size] ?? 20;
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-ui.icon :name="$icon" :size="$iconSize" />
    </a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
        <x-ui.icon :name="$icon" :size="$iconSize" />
    </button>
@endif
