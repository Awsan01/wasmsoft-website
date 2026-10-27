{{--
    Home page. Sections without published content in this language are left
    out entirely (no empty states, no filler).
--}}
@php
    $locale = app()->getLocale();
@endphp

<x-layouts.site :seo="$seo">
    {{-- Hero: CSS-only, so it costs no image download. --}}
    <section class="surface-dark relative isolate overflow-hidden bg-purple-950" aria-labelledby="hero-title">
        <div class="bg-diagonal pointer-events-none absolute inset-0 opacity-60" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-48 -inset-e-40 size-[38rem] rounded-full bg-purple-700/45 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-56 -inset-s-40 size-[30rem] rounded-full bg-purple-800/50 blur-3xl" aria-hidden="true"></div>

        <div class="container-page relative grid items-center gap-12 py-section lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <div class="grid content-start gap-6">
                @if ($tagline)
                    <p class="flex items-center gap-2.5 text-eyebrow text-on-dark-muted">
                        <span class="slash-mark" aria-hidden="true"></span>
                        {{ $tagline }}
                    </p>
                @endif

                <h1 id="hero-title" class="text-display text-balance text-white">{{ $page?->title ?: __('site.home.hero_title') }}</h1>

                <p class="max-w-xl text-lead text-on-dark-muted">{{ $page?->summary ?: __('site.home.hero_lead') }}</p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <x-ui.button :href="route('site.contact')" variant="on-dark" size="lg" icon-end="arrow-right">{{ __('site.nav.cta') }}</x-ui.button>
                    @if ($projects->isNotEmpty())
                        <x-ui.button :href="route('site.projects.index')" variant="on-dark-outline" size="lg">{{ __('site.home.hero_secondary') }}</x-ui.button>
                    @elseif ($services->isNotEmpty())
                        <x-ui.button :href="route('site.services.index')" variant="on-dark-outline" size="lg">{{ __('site.home.services_all') }}</x-ui.button>
                    @endif
                </div>
            </div>

            {{-- Decorative composition echoing the angular logo symbol. --}}
            <div class="relative mx-auto hidden aspect-square w-full max-w-md lg:block" aria-hidden="true">
                <div class="notch-corner absolute inset-[12%] rotate-6 rounded-2xl bg-brand-gradient shadow-lg ring-1 ring-white/10"></div>
                <div class="notch-corner absolute inset-[22%] -rotate-3 rounded-2xl border border-white/20 bg-white/5 backdrop-blur-sm">
                    <div class="bg-diagonal absolute inset-0 rounded-2xl"></div>
                </div>
                <div class="absolute inset-s-[30%] top-[18%] h-[64%] w-3 rotate-[35deg] rounded-full bg-white/80 shadow-md"></div>
                <div class="notch-corner absolute inset-e-[6%] bottom-[8%] size-[26%] rounded-xl bg-purple-300/90"></div>
                <div class="notch-corner absolute inset-s-[4%] top-[4%] size-[16%] rounded-lg border border-white/30"></div>
            </div>
        </div>
    </section>

    @if ($intro->toHtml() !== '' || $services->isNotEmpty())
        <section class="py-section" aria-labelledby="home-services">
            <div class="container-page grid gap-10">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-end">
                    <x-ui.section-heading heading-id="home-services" :eyebrow="__('site.home.services_eyebrow')" :title="__('site.home.services_title')" />
                    @if ($intro->toHtml() !== '')
                        <div class="prose text-muted">{{ $intro }}</div>
                    @endif
                </div>

                @if ($services->isNotEmpty())
                    <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($services as $service)
                            <li><x-site.service-card :service="$service" :heading-level="3" /></li>
                        @endforeach
                    </ul>

                    <div>
                        <x-ui.button :href="route('site.services.index')" variant="outline" icon-end="arrow-right">{{ __('site.home.services_all') }}</x-ui.button>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($differentiators->isNotEmpty())
        <section class="relative overflow-hidden border-y border-border bg-lavender py-section" aria-labelledby="home-why">
            <div class="bg-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="container-page relative grid gap-10">
                <x-ui.section-heading heading-id="home-why" :eyebrow="__('site.home.why_eyebrow')" :title="__('site.home.why_title')" />

                <ul class="grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($differentiators as $highlight)
                        <li class="grid content-start gap-3">
                            <span class="notch-corner grid size-12 place-items-center rounded-lg bg-surface text-primary shadow-xs">
                                <x-ui.icon :name="$highlight->icon?->value ?? 'check-circle'" :size="24" />
                            </span>
                            <h3 class="text-h3">{{ $highlight->localized('title') }}</h3>
                            @if ($description = $highlight->localized('description'))
                                <p class="text-muted">{{ $description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($figures->isNotEmpty())
        <section class="py-section-sm" aria-labelledby="home-figures">
            <div class="container-page grid gap-8">
                <h2 id="home-figures" class="sr-only">{{ __('site.home.figures_title') }}</h2>
                <dl class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($figures as $figure)
                        <div class="grid content-start gap-2 border-s-2 border-secondary ps-5">
                            <dt class="order-2 text-sm text-muted">{{ $figure->localized('title') }}</dt>
                            <dd class="order-1 text-h1 text-primary"><bdi>{{ $figure->figure }}</bdi></dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="py-section" aria-labelledby="home-projects">
            <div class="container-page grid gap-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading heading-id="home-projects" :eyebrow="__('site.home.projects_eyebrow')" :title="__('site.home.projects_title')" />
                    <x-ui.button :href="route('site.projects.index')" variant="outline" icon-end="arrow-right">{{ __('site.home.projects_all') }}</x-ui.button>
                </div>

                <ul class="grid gap-6 md:grid-cols-3">
                    @foreach ($projects as $project)
                        <li><x-site.project-card :project="$project" :heading-level="3" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="border-y border-border bg-canvas py-section" aria-labelledby="home-testimonials">
            <div class="container-page grid gap-10">
                <x-ui.section-heading heading-id="home-testimonials" :eyebrow="__('site.home.testimonials_eyebrow')" :title="__('site.home.testimonials_title')" />

                <ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <li>
                            <figure class="flex h-full flex-col gap-6 rounded-xl border border-border bg-surface p-7 shadow-xs">
                                <x-ui.icon name="quote" :size="28" class="text-purple-300" />
                                <blockquote class="flex-1 text-lg text-ink">
                                    <p>{{ $testimonial->localized('quote') }}</p>
                                </blockquote>
                                <figcaption class="flex items-center gap-3">
                                    @if ($testimonial->photo)
                                        <img src="{{ $testimonial->photo->urlForWidth(480) }}" alt="" width="48" height="48" loading="lazy" class="size-12 rounded-full object-cover">
                                    @endif
                                    <span class="grid gap-0.5 text-sm">
                                        <span class="font-semibold text-ink"><bdi>{{ $testimonial->author_name }}</bdi></span>
                                        @php
                                            $role = implode(', ', array_filter([$testimonial->localized('author_role'), $testimonial->company]));
                                        @endphp
                                        @if ($role !== '')
                                            <span class="text-muted"><bdi>{{ $role }}</bdi></span>
                                        @endif
                                    </span>
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($posts->isNotEmpty())
        <section class="py-section" aria-labelledby="home-news">
            <div class="container-page grid gap-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-ui.section-heading heading-id="home-news" :eyebrow="__('site.home.news_eyebrow')" :title="__('site.home.news_title')" />
                    <x-ui.button :href="route('site.news.index')" variant="outline" icon-end="arrow-right">{{ __('site.home.news_all') }}</x-ui.button>
                </div>

                <ul class="grid gap-6 md:grid-cols-3">
                    @foreach ($posts as $post)
                        <li><x-site.post-card :post="$post" :heading-level="3" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
