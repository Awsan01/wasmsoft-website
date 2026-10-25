{{-- A titled card grouping related fields or details on dashboard pages. --}}
@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['grid content-start gap-5 rounded-xl border border-border bg-surface p-6 shadow-xs']) }}>
    @if ($title)
        <div class="grid gap-1">
            <h2 class="text-h3">{{ $title }}</h2>
            @if ($description)
                <p class="text-sm text-muted">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
