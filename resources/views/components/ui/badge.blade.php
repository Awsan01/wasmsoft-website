{{--
    Status / category label. Always carries text, so meaning never relies
    on colour alone; `dot` adds a coloured marker for quick scanning.
--}}
@props([
    'tone' => 'neutral',
    'dot' => false,
    'icon' => null,
])

@php
    $tones = [
        'neutral' => 'border-border bg-canvas text-ink',
        'primary' => 'border-purple-200 bg-lavender text-primary',
        'success' => 'border-success-line bg-success-soft text-success',
        'warning' => 'border-warning-line bg-warning-soft text-warning-ink',
        'error' => 'border-error-line bg-error-soft text-error',
    ];
    $dots = [
        'neutral' => 'bg-muted',
        'primary' => 'bg-secondary',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error' => 'bg-error',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
    $tones[$tone] ?? $tones['neutral'],
]) }}>
    @if ($dot)
        <span @class(['size-1.5 rounded-full', $dots[$tone] ?? $dots['neutral']]) aria-hidden="true"></span>
    @endif
    @if ($icon)
        <x-ui.icon :name="$icon" :size="14" />
    @endif
    {{ $slot }}
</span>
