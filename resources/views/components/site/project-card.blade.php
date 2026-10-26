{{--
    A project in a grid: cover image (or a brand pattern), category, title
    and summary. The title link covers the whole card.
--}}
@props([
    'project',
    'headingLevel' => 2,
])

@php
    $locale = app()->getLocale();
    $translation = $project->translation($locale);
    $category = $project->category?->translation($locale);
    $tag = 'h'.$headingLevel;
@endphp

<article {{ $attributes->class(['group relative flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-xs transition-base hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary']) }}>
    <div class="relative aspect-16/10 overflow-hidden bg-brand-gradient">
        @if ($project->cover)
            <x-media.image :media="$project->cover" alt="" sizes="(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw" class="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-105" />
        @else
            <div class="bg-diagonal absolute inset-0" aria-hidden="true"></div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-3 p-6">
        @if ($category)
            <p class="text-eyebrow text-secondary">{{ $category->name }}</p>
        @endif

        <{{ $tag }} class="text-h3">
            <a href="{{ $project->publicUrl($locale) }}" class="after:absolute after:inset-0 after:rounded-xl focus:outline-none">{{ $translation?->title }}</a>
        </{{ $tag }}>

        @if ($translation?->summary)
            <p class="text-sm text-muted">{{ $translation->summary }}</p>
        @endif

        <span class="mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-semibold text-secondary" aria-hidden="true">
            {{ __('site.projects.view') }}
            <x-ui.icon name="arrow-right" :size="16" class="transition-base group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
        </span>
    </div>
</article>
