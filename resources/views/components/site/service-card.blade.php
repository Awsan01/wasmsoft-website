{{--
    A service in a grid. The whole card is clickable through one link
    (its title), so screen readers hear a single, meaningful link.
--}}
@props([
    'service',
    'headingLevel' => 2,
])

@php
    $locale = app()->getLocale();
    $translation = $service->translation($locale);
    $tag = 'h'.$headingLevel;
@endphp

<article {{ $attributes->class(['group relative flex h-full flex-col gap-5 rounded-xl border border-border bg-surface p-7 shadow-xs transition-base hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary']) }}>
    <span class="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
        <x-ui.icon :name="$service->icon?->value ?? 'layers'" :size="24" />
    </span>

    <div class="grid gap-2">
        <{{ $tag }} class="text-h3">
            <a href="{{ $service->publicUrl($locale) }}" class="after:absolute after:inset-0 after:rounded-xl focus:outline-none">{{ $translation?->title }}</a>
        </{{ $tag }}>
        @if ($translation?->summary)
            <p class="text-sm text-muted">{{ $translation->summary }}</p>
        @endif
    </div>

    <span class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-secondary" aria-hidden="true">
        {{ __('ui.learn_more') }}
        <x-ui.icon name="arrow-right" :size="16" class="transition-base group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
    </span>
</article>
