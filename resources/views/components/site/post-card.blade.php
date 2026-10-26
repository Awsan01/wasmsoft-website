{{--
    A news article in a grid: cover image (or a brand pattern), category,
    title, excerpt, publication date and reading time. The title link
    covers the whole card.
--}}
@props([
    'post',
    'headingLevel' => 2,
])

@php
    $locale = app()->getLocale();
    $translation = $post->translation($locale);
    $category = $post->category?->translation($locale);
    $minutes = $translation?->readingMinutes() ?? 1;
    $tag = 'h'.$headingLevel;
@endphp

<article {{ $attributes->class(['group relative flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-xs transition-base hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary']) }}>
    <div class="relative aspect-16/9 overflow-hidden bg-brand-gradient">
        @if ($post->cover)
            <x-media.image :media="$post->cover" alt="" sizes="(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw" class="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-105" />
        @else
            <div class="bg-diagonal absolute inset-0" aria-hidden="true"></div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-3 p-6">
        @if ($category)
            <p class="text-eyebrow text-secondary">{{ $category->name }}</p>
        @endif

        <{{ $tag }} class="text-h3">
            <a href="{{ $post->publicUrl($locale) }}" class="after:absolute after:inset-0 after:rounded-xl focus:outline-none">{{ $translation?->title }}</a>
        </{{ $tag }}>

        @if ($translation?->summary)
            <p class="text-sm text-muted">{{ $translation->summary }}</p>
        @endif

        <p class="mt-auto flex flex-wrap items-center gap-x-2 gap-y-1 pt-2 text-sm text-muted">
            @if ($post->published_at)
                <x-ui.time :value="$post->published_at" style="medium" />
                <span aria-hidden="true">·</span>
            @endif
            <span>{{ trans_choice('site.news.reading_time', $minutes, ['count' => app(\App\Localization\Formatter::class)->number($minutes)]) }}</span>
        </p>
    </div>
</article>
