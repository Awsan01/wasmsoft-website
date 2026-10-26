{{-- News listing; also the category and tag pages. --}}
@php
    $locale = app()->getLocale();
@endphp

<x-layouts.site :seo="$seo">
    <x-site.page-hero :title="$title" :lead="$lead" :eyebrow="$eyebrow" :breadcrumbs="$breadcrumbs">
        @if ($categories->isNotEmpty())
            <nav aria-label="{{ __('site.news.filter') }}">
                <ul class="flex flex-wrap gap-2">
                    <li>
                        <x-site.filter-chip :href="route('site.news.index')" :current="request()->routeIs('site.news.index')">{{ __('ui.all') }}</x-site.filter-chip>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <x-site.filter-chip :href="(string) $category->publicUrl($locale)" :current="(bool) $activeCategory?->is($category)">{{ $category->translation($locale)?->name }}</x-site.filter-chip>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </x-site.page-hero>

    <section class="container-page grid gap-10 py-section" aria-label="{{ $title }}">
        @if ($posts->isEmpty())
            <x-ui.empty-state :title="__('site.news.empty')" icon="newspaper">
                @unless (request()->routeIs('site.news.index'))
                    <x-slot:actions>
                        <x-ui.button :href="route('site.news.index')" variant="outline" size="sm">{{ __('site.news.show_all') }}</x-ui.button>
                    </x-slot:actions>
                @endunless
            </x-ui.empty-state>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li><x-site.post-card :post="$post" /></li>
                @endforeach
            </ul>

            {{ $posts->links() }}
        @endif
    </section>

    <x-site.cta-band />
</x-layouts.site>
