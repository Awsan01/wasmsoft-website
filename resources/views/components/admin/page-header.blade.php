{{-- Dashboard page title with optional description and actions (end-aligned). --}}
@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="grid min-w-0 gap-1.5">
        <h1 class="text-h2">{{ $title }}</h1>
        @if ($description)
            <p class="max-w-3xl text-sm text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
