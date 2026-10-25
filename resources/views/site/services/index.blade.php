<x-layouts.site :seo="$seo">
    <x-site.page-hero
        :title="$title"
        :lead="$lead"
        :eyebrow="__('site.services.eyebrow')"
        :breadcrumbs="[
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.services'), 'url' => null],
        ]"
    />

    <section class="container-page py-section" aria-label="{{ $title }}">
        @if ($services->isEmpty())
            <x-ui.empty-state :title="__('site.services.empty')" icon="layers" />
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <li><x-site.service-card :service="$service" /></li>
                @endforeach
            </ul>
        @endif
    </section>

    <x-site.cta-band />
</x-layouts.site>
