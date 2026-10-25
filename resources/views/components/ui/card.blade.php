{{--
    Surface container. `interactive` adds the hover treatment used for
    clickable cards (the whole card should then contain one primary link).
--}}
@props([
    'as' => 'div',
    'padding' => 'md',
    'interactive' => false,
])

@php
    $paddings = ['none' => '', 'sm' => 'p-4', 'md' => 'p-6', 'lg' => 'p-8'];
@endphp

<{{ $as }} {{ $attributes->class([
    'rounded-xl border border-border bg-surface shadow-xs',
    $paddings[$padding] ?? $paddings['md'],
    'transition-base hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md' => $interactive,
]) }}>
    {{ $slot }}
</{{ $as }}>
