<x-layouts.site :seo="$seo" :preview="$preview ?? false">
    <x-site.page-hero :title="$translation->title" :lead="$translation->summary" :breadcrumbs="$breadcrumbs" />

    @if ($body->toHtml() !== '')
        <div class="container-page py-section">
            <div class="prose">{{ $body }}</div>
        </div>
    @endif
</x-layouts.site>
