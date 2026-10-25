@props([
    'title',
    'icon' => 'inbox',
])

<div {{ $attributes->class(['flex flex-col items-center gap-3 rounded-xl border border-dashed border-purple-300 bg-canvas px-6 py-12 text-center']) }}>
    <span class="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
        <x-ui.icon :name="$icon" :size="24" />
    </span>
    <h2 class="text-h3">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div class="max-w-md text-sm text-muted">{{ $slot }}</div>
    @endif
    @isset($actions)
        <div class="mt-2 flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @endisset
</div>
