@php
    $formatter = app(\App\Localization\Formatter::class);
@endphp

<x-layouts.site :seo="$seo" :preview="$preview">
    <x-site.page-hero :title="$translation->title" :lead="$translation->summary" :eyebrow="$category?->name" :breadcrumbs="$breadcrumbs" />

    <div class="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid min-w-0 content-start gap-10">
            @if ($project->cover)
                <x-media.image
                    :media="$project->cover"
                    :alt="$project->cover->altText() ?? ''"
                    sizes="(min-width: 64rem) 46rem, 100vw"
                    class="w-full rounded-2xl border border-border object-cover"
                    eager
                />
            @endif

            @if ($body->toHtml() !== '')
                <div class="prose">{{ $body }}</div>
            @endif

            @if ($project->gallery->isNotEmpty())
                <section aria-labelledby="project-gallery" class="grid gap-5">
                    <h2 id="project-gallery" class="text-h3">{{ __('site.projects.gallery') }}</h2>
                    <ul class="grid gap-4 sm:grid-cols-2">
                        @foreach ($project->gallery as $image)
                            <li>
                                <a href="{{ $image->url() }}" class="block overflow-hidden rounded-xl border border-border" target="_blank" rel="noopener">
                                    <x-media.image :media="$image" sizes="(min-width: 64rem) 23rem, (min-width: 40rem) 50vw, 100vw" class="aspect-4/3 w-full object-cover transition-base hover:scale-105" />
                                    <span class="sr-only">{{ __('site.projects.open_image') }} {{ __('ui.opens_in_new_tab') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="grid content-start gap-6">
            <x-ui.card padding="lg">
                <h2 class="text-h3">{{ __('site.projects.details') }}</h2>
                <dl class="mt-5 grid gap-4 text-sm">
                    @if ($project->client_name)
                        <div class="grid gap-0.5">
                            <dt class="text-muted">{{ __('site.projects.client') }}</dt>
                            <dd class="font-semibold"><bdi>{{ $project->client_name }}</bdi></dd>
                        </div>
                    @endif
                    @if ($category)
                        <div class="grid gap-0.5">
                            <dt class="text-muted">{{ __('site.projects.category') }}</dt>
                            <dd class="font-semibold">{{ $category->name }}</dd>
                        </div>
                    @endif
                    @if ($project->completed_on)
                        <div class="grid gap-0.5">
                            <dt class="text-muted">{{ __('site.projects.completed') }}</dt>
                            <dd class="font-semibold"><x-ui.time :value="$project->completed_on" style="medium" /></dd>
                        </div>
                    @endif
                    @if ($project->technologies)
                        <div class="grid gap-2">
                            <dt class="text-muted">{{ __('site.projects.technologies') }}</dt>
                            <dd>
                                <ul class="flex flex-wrap gap-1.5" dir="ltr">
                                    @foreach ($project->technologies as $technology)
                                        <li><x-ui.badge tone="primary">{{ $technology }}</x-ui.badge></li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($project->project_url)
                    <x-ui.button :href="$project->project_url" variant="outline" icon-end="external-link" class="mt-6" target="_blank" rel="noopener noreferrer" block>
                        {{ __('site.projects.visit') }}
                        <span class="sr-only">{{ __('ui.opens_in_new_tab') }}</span>
                    </x-ui.button>
                @endif
            </x-ui.card>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="border-t border-border bg-canvas py-section" aria-labelledby="related-projects">
            <div class="container-page grid gap-8">
                <h2 id="related-projects" class="text-h2">{{ __('site.projects.related') }}</h2>
                <ul class="grid gap-6 md:grid-cols-3">
                    @foreach ($related as $item)
                        <li><x-site.project-card :project="$item" :heading-level="3" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
