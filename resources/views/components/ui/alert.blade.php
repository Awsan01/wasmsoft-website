{{--
    Inline message. Errors are announced assertively (role="alert"); other
    tones politely (role="status"). Set `dismissible` to add a close button.
--}}
@props([
    'tone' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'info' => ['border-purple-200 bg-lavender text-ink', 'info', 'text-secondary'],
        'success' => ['border-success-line bg-success-soft text-ink', 'check-circle', 'text-success'],
        'warning' => ['border-warning-line bg-warning-soft text-ink', 'alert-triangle', 'text-warning'],
        'error' => ['border-error-line bg-error-soft text-ink', 'alert-circle', 'text-error'],
    ];
    [$classes, $icon, $iconClass] = $tones[$tone] ?? $tones['info'];
@endphp

<div
    role="{{ $tone === 'error' ? 'alert' : 'status' }}"
    @if ($dismissible) data-dismissible @endif
    {{ $attributes->class(['flex items-start gap-3 rounded-lg border p-4 text-sm', $classes]) }}
>
    <x-ui.icon :name="$icon" :size="20" @class(['mt-px', $iconClass]) />
    <div class="grid min-w-0 flex-1 gap-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="text-ink/85">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" data-dismiss class="-m-1 rounded-sm p-1 text-muted transition-base hover:text-ink" aria-label="{{ __('ui.dismiss') }}">
            <x-ui.icon name="x" :size="18" />
        </button>
    @endif
</div>
