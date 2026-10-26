@php
    $locale = app()->getLocale();
    $minutes = $translation->readingMinutes();
@endphp

<x-layouts.site :seo="$seo" :preview="$preview">
    <x-site.page-hero :title="$translation->title" :lead="$translation->summary" :eyebrow="$category?->name" :breadcrumbs="$breadcrumbs">
        <ul class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted" aria-label="{{ __('site.news.about_article') }}">
            @if ($post->author)
                <li class="flex items-center gap-1.5">
                    <x-ui.icon name="user" :size="16" />
                    <span><span class="sr-only">{{ __('site.news.author') }}: </span><bdi>{{ $post->author->name }}</bdi></span>
                </li>
            @endif
            @if ($post->published_at)
                <li class="flex items-center gap-1.5">
                    <x-ui.icon name="calendar" :size="16" />
                    <span><span class="sr-only">{{ __('site.news.published') }}: </span><x-ui.time :value="$post->published_at" /></span>
                </li>
            @endif
            <li class="flex items-center gap-1.5">
                <x-ui.icon name="clock" :size="16" />
                <span>{{ trans_choice('site.news.reading_time', $minutes, ['count' => app(\App\Localization\Formatter::class)->number($minutes)]) }}</span>
            </li>
        </ul>
    </x-site.page-hero>

    <div class="container-page grid gap-10 py-section">
        <div class="mx-auto grid w-full max-w-3xl min-w-0 gap-10">
            @if ($post->cover)
                <x-media.image
                    :media="$post->cover"
                    :alt="$post->cover->altText() ?? ''"
                    sizes="(min-width: 48rem) 48rem, 100vw"
                    class="w-full rounded-2xl border border-border object-cover"
                    eager
                />
            @endif

            @if ($body->toHtml() !== '')
                <div class="prose">{{ $body }}</div>
            @endif

            @if ($category || $tags->isNotEmpty())
                <footer class="grid gap-4 border-t border-border pt-6">
                    @if ($category)
                        <p class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="text-muted">{{ __('site.news.category') }}:</span>
                            @if ($url = $post->category?->publicUrl($locale))
                                <a href="{{ $url }}" class="font-semibold text-secondary hover:underline">{{ $category->name }}</a>
                            @else
                                <span class="font-semibold">{{ $category->name }}</span>
                            @endif
                        </p>
                    @endif

                    @if ($tags->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span id="article-tags" class="text-muted">{{ __('site.news.tags') }}:</span>
                            <ul class="flex flex-wrap gap-2" aria-labelledby="article-tags">
                                @foreach ($tags as $tag)
                                    <li>
                                        <a href="{{ $tag->publicUrl($locale) }}" class="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-lavender px-3 py-1 font-semibold text-primary transition-base hover:border-primary hover:underline">
                                            <x-ui.icon name="tag" :size="14" />
                                            {{ $tag->translation($locale)?->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </footer>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="border-t border-border bg-canvas py-section" aria-labelledby="related-posts">
            <div class="container-page grid gap-8">
                <h2 id="related-posts" class="text-h2">{{ __('site.news.related') }}</h2>
                <ul class="grid gap-6 md:grid-cols-3">
                    @foreach ($related as $item)
                        <li><x-site.post-card :post="$item" :heading-level="3" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
