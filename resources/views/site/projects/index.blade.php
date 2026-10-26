<x-layouts.site :seo="$seo">
    <x-site.page-hero
        :title="$title"
        :lead="$lead"
        :eyebrow="__('site.projects.eyebrow')"
        :breadcrumbs="[
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.projects'), 'url' => null],
        ]"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            @if ($categories->isNotEmpty())
                <nav aria-label="{{ __('site.projects.filter') }}">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <a
                                href="{{ route('site.projects.index', array_filter(['q' => $search])) }}"
                                @if (! $activeCategory) aria-current="page" @endif
                                class="inline-flex h-control-sm items-center rounded-full border border-border bg-surface px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-white"
                            >{{ __('ui.all') }}</a>
                        </li>
                        @foreach ($categories as $category)
                            @php
                                $categoryTranslation = $category->translation();
                            @endphp
                            <li>
                                <a
                                    href="{{ route('site.projects.index', array_filter(['category' => $categoryTranslation?->slug, 'q' => $search])) }}"
                                    @if ($activeCategory?->is($category)) aria-current="page" @endif
                                    class="inline-flex h-control-sm items-center rounded-full border border-border bg-surface px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-white"
                                >{{ $categoryTranslation?->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            <form method="GET" action="{{ route('site.projects.index') }}" role="search" class="flex gap-2">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->translation()?->slug }}">
                @endif
                <label for="project-search" class="sr-only">{{ __('site.projects.search') }}</label>
                <input id="project-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="{{ __('site.projects.search') }}" class="form-control sm:w-64">
                <x-ui.button type="submit" variant="outline" icon="search">{{ __('ui.search') }}</x-ui.button>
            </form>
        </div>
    </x-site.page-hero>

    <section class="container-page grid gap-10 py-section" aria-label="{{ $title }}">
        @if ($projects->isEmpty())
            <x-ui.empty-state :title="$search !== '' ? __('site.projects.no_results') : __('site.projects.empty')" icon="briefcase">
                @if ($search !== '' || $activeCategory)
                    <x-slot:actions>
                        <x-ui.button :href="route('site.projects.index')" variant="outline" size="sm">{{ __('site.projects.show_all') }}</x-ui.button>
                    </x-slot:actions>
                @endif
            </x-ui.empty-state>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <li><x-site.project-card :project="$project" /></li>
                @endforeach
            </ul>

            {{ $projects->links() }}
        @endif
    </section>

    <x-site.cta-band />
</x-layouts.site>
