{{--
    Opening band of an inner page: breadcrumbs, optional eyebrow, title and
    lead paragraph on the tinted brand background. Extra content (filters,
    meta) goes in the default slot.
--}}
@props([
    'title',
    'lead' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
])

<section {{ $attributes->class(['relative overflow-hidden border-b border-border bg-lavender']) }}>
    <div class="bg-grid absolute inset-0" aria-hidden="true"></div>

    <div class="container-page relative grid gap-6 py-section-sm">
        <x-ui.breadcrumbs :items="$breadcrumbs" />

        <div class="grid max-w-3xl gap-4">
            @if ($eyebrow)
                <p class="flex items-center gap-2.5 text-eyebrow text-secondary">
                    <span class="slash-mark" aria-hidden="true"></span>
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="text-h1">{{ $title }}</h1>

            @if ($lead)
                <p class="text-lead text-muted">{{ $lead }}</p>
            @endif
        </div>

        {{ $slot }}
    </div>
</section>
