{{--
    A page with the "about" template: the page text (introduction, mission,
    vision), then the company's values, team and history when they exist
    in this language.
--}}
<x-layouts.site :seo="$seo" :preview="$preview ?? false">
    <x-site.page-hero :title="$translation->title" :lead="$translation->summary" :breadcrumbs="$breadcrumbs" />

    @if ($body->toHtml() !== '')
        <div class="container-page py-section">
            <div class="prose">{{ $body }}</div>
        </div>
    @endif

    @if ($values->isNotEmpty())
        <section class="relative overflow-hidden border-y border-border bg-lavender py-section" aria-labelledby="about-values">
            <div class="bg-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="container-page relative grid gap-10">
                <x-ui.section-heading heading-id="about-values" :eyebrow="__('site.about.values_eyebrow')" :title="__('site.about.values_title')" />

                <ul class="grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($values as $value)
                        <li class="grid content-start gap-3">
                            <span class="notch-corner grid size-12 place-items-center rounded-lg bg-surface text-primary shadow-xs">
                                <x-ui.icon :name="$value->icon?->value ?? 'check-circle'" :size="24" />
                            </span>
                            <h3 class="text-h3">{{ $value->localized('title') }}</h3>
                            @if ($description = $value->localized('description'))
                                <p class="text-muted">{{ $description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($team->isNotEmpty())
        <section class="py-section" aria-labelledby="about-team">
            <div class="container-page grid gap-10">
                <x-ui.section-heading heading-id="about-team" :eyebrow="__('site.about.team_eyebrow')" :title="__('site.about.team_title')" />

                <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($team as $member)
                        <li class="flex h-full flex-col gap-4 rounded-xl border border-border bg-surface p-6 shadow-xs">
                            @if ($member->photo)
                                <img src="{{ $member->photo->urlForWidth(480) }}" alt="" width="80" height="80" loading="lazy" class="size-20 rounded-full object-cover">
                            @else
                                <span class="grid size-20 place-items-center rounded-full bg-lavender text-h3 text-primary" aria-hidden="true">{{ mb_substr($member->name, 0, 1) }}</span>
                            @endif
                            <div class="grid gap-1">
                                <h3 class="text-h3"><bdi>{{ $member->name }}</bdi></h3>
                                <p class="text-sm font-semibold text-secondary">{{ $member->localized('role') }}</p>
                            </div>
                            @if ($bio = $member->localized('bio'))
                                <p class="text-sm text-muted">{{ $bio }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($milestones->isNotEmpty())
        <section class="border-t border-border bg-canvas py-section" aria-labelledby="about-history">
            <div class="container-page grid gap-10">
                <x-ui.section-heading heading-id="about-history" :eyebrow="__('site.about.history_eyebrow')" :title="__('site.about.history_title')" />

                <ol class="relative grid max-w-3xl gap-8 border-s-2 border-purple-200 ps-8">
                    @foreach ($milestones as $milestone)
                        <li class="relative grid gap-1">
                            <span class="absolute inset-s-[-2.6rem] top-1.5 size-4 rounded-full border-4 border-canvas bg-secondary" aria-hidden="true"></span>
                            <p class="text-sm font-bold text-secondary"><time datetime="{{ $milestone->year }}">{{ $milestone->year }}</time></p>
                            <h3 class="text-h3">{{ $milestone->localized('title') }}</h3>
                            @if ($description = $milestone->localized('description'))
                                <p class="text-muted">{{ $description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
