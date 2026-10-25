@props([
    'tabsId',
    'key',
    'title' => null,
])

<section
    role="tabpanel"
    id="{{ $tabsId }}-panel-{{ $key }}"
    aria-labelledby="{{ $tabsId }}-tab-{{ $key }}"
    data-tab-panel="{{ $key }}"
    tabindex="0"
    {{ $attributes->class(['grid gap-5 rounded-md']) }}
>
    @if ($title)
        {{-- Heading shown only when JavaScript is unavailable and all panels are stacked. --}}
        <h3 class="text-h3" data-tab-fallback-title>{{ $title }}</h3>
    @endif
    {{ $slot }}
</section>
