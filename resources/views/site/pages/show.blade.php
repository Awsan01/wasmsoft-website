<x-layouts.site :seo="$seo">
    <x-site.page-hero
        :title="$translation->title"
        :lead="$translation->summary"
        :breadcrumbs="[
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => $translation->title, 'url' => null],
        ]"
    />

    @if ($body->toHtml() !== '')
        <div class="container-page py-section">
            <div class="prose">{{ $body }}</div>
        </div>
    @endif
</x-layouts.site>
