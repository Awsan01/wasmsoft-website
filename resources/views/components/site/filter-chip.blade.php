{{-- A link in a row of filter chips (e.g. categories); `current` marks the active one. --}}
@props([
    'href',
    'current' => false,
])

<a
    href="{{ $href }}"
    @if ($current) aria-current="page" @endif
    {{ $attributes->class(['inline-flex h-control-sm items-center rounded-full border border-border bg-surface px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-white']) }}
>{{ $slot }}</a>
