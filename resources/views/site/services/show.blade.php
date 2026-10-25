<x-layouts.site :seo="$seo" :preview="$preview">
    <x-site.page-hero :title="$translation->title" :lead="$translation->summary" :breadcrumbs="$breadcrumbs">
        <div>
            <x-ui.button :href="$contactUrl" icon-end="arrow-right">{{ __('site.services.cta_button') }}</x-ui.button>
        </div>
    </x-site.page-hero>

    <div class="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid min-w-0 content-start gap-10">
            @if ($service->image)
                <x-media.image
                    :media="$service->image"
                    :alt="$service->image->altText() ?? ''"
                    sizes="(min-width: 64rem) 46rem, 100vw"
                    class="w-full rounded-2xl border border-border object-cover"
                    eager
                />
            @endif

            @if ($body->toHtml() !== '')
                <div class="prose">{{ $body }}</div>
            @endif
        </div>

        <aside class="grid content-start gap-6">
            @if ($benefits !== [])
                <x-ui.card padding="lg">
                    <h2 class="text-h3">{{ __('site.services.benefits') }}</h2>
                    <ul class="mt-5 grid gap-3">
                        @foreach ($benefits as $benefit)
                            <li class="flex items-start gap-3">
                                <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                                    <x-ui.icon name="check" :size="14" />
                                </span>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif

            <div class="surface-dark relative overflow-hidden rounded-2xl bg-brand-gradient p-7">
                <div class="bg-diagonal pointer-events-none absolute inset-0 opacity-70" aria-hidden="true"></div>
                <div class="relative grid gap-4">
                    <h2 class="text-h3 text-white">{{ __('site.services.cta_title') }}</h2>
                    <p class="text-sm text-on-dark-muted">{{ __('site.services.cta_text') }}</p>
                    <div>
                        <x-ui.button :href="$contactUrl" variant="on-dark" icon-end="arrow-right">{{ __('site.services.cta_button') }}</x-ui.button>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    @if ($others->isNotEmpty())
        <section class="border-t border-border bg-canvas py-section" aria-labelledby="more-services">
            <div class="container-page grid gap-8">
                <h2 id="more-services" class="text-h2">{{ __('site.services.more') }}</h2>
                <ul class="grid gap-6 md:grid-cols-3">
                    @foreach ($others as $other)
                        <li><x-site.service-card :service="$other" :heading-level="3" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.site>
