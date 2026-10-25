{{-- Closing call to action on the brand gradient, leading to the contact page. --}}
@props([
    'title' => null,
    'text' => null,
    'href' => null,
])

<section {{ $attributes->class(['surface-dark relative overflow-hidden bg-brand-gradient']) }}>
    <div class="bg-diagonal pointer-events-none absolute inset-0 opacity-70" aria-hidden="true"></div>

    <div class="container-page relative flex flex-col items-start gap-6 py-section-sm md:flex-row md:items-center md:justify-between">
        <div class="grid max-w-2xl gap-3">
            <h2 class="text-h2 text-white">{{ $title ?? __('site.cta.title') }}</h2>
            <p class="text-lead text-on-dark-muted">{{ $text ?? __('site.cta.text') }}</p>
        </div>

        <x-ui.button :href="$href ?? route('site.contact')" variant="on-dark" size="lg" icon-end="arrow-right">
            {{ __('site.cta.button') }}
        </x-ui.button>
    </div>
</section>
