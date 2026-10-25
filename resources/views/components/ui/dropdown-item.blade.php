{{-- An entry inside x-ui.dropdown: a link when `href` is set, otherwise a button. --}}
@props([
    'href' => null,
    'icon' => null,
    'active' => false,
    'tone' => 'default',
    'type' => 'button',
])

@php
    $classes = implode(' ', [
        'flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm transition-base',
        $tone === 'danger' ? 'text-error hover:bg-error-soft' : 'text-ink hover:bg-lavender hover:text-primary',
        $active ? 'bg-lavender font-semibold text-primary' : '',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($active) aria-current="true" @endif {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="18" class="text-muted" />@endif
        <span class="min-w-0 flex-1">{{ $slot }}</span>
        @if ($active)<x-ui.icon name="check" :size="16" class="text-primary" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="18" @class(['text-muted' => $tone !== 'danger']) />@endif
        <span class="min-w-0 flex-1">{{ $slot }}</span>
    </button>
@endif
