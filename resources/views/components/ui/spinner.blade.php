{{-- Loading indicator. With a `label` it is announced as a status message. --}}
@props([
    'size' => 20,
    'label' => null,
])

<span @if ($label) role="status" @endif {{ $attributes->class(['inline-flex items-center gap-2']) }}>
    <svg class="animate-spin" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.2" stroke-width="3" />
        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
    </svg>
    @if ($label)
        <span class="sr-only">{{ $label }}</span>
    @endif
</span>
