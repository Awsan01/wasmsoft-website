{{--
    Accessible tabs (WAI-ARIA tabs pattern, automatic activation).
    `tabs` is an ordered map of key => label (or ['label' => …, 'badge' => …]).
    Provide one panel per tab with <x-ui.tab-panel :tabs-id="$id" key="…">.
    Without JavaScript every panel stays visible, so no content is lost.
--}}
@props([
    'id',
    'tabs' => [],
    'label' => null,
    'active' => null,
])

@php
    $active ??= array_key_first($tabs);
@endphp

<div data-tabs {{ $attributes->class(['grid gap-5']) }}>
    <div role="tablist" @if ($label) aria-label="{{ $label }}" @endif class="flex flex-wrap gap-1 border-b border-border" data-tablist hidden>
        @foreach ($tabs as $key => $tab)
            @php
                $tabLabel = is_array($tab) ? $tab['label'] : $tab;
                $badge = is_array($tab) ? ($tab['badge'] ?? null) : null;
                $isActive = (string) $key === (string) $active;
            @endphp
            <button
                type="button"
                role="tab"
                id="{{ $id }}-tab-{{ $key }}"
                aria-controls="{{ $id }}-panel-{{ $key }}"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                tabindex="{{ $isActive ? '0' : '-1' }}"
                data-tab="{{ $key }}"
                class="relative -mb-px flex items-center gap-2 border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-muted transition-base hover:text-primary aria-selected:border-primary aria-selected:text-primary"
            >
                <span>{{ $tabLabel }}</span>
                @if ($badge)
                    {{ $badge }}
                @endif
            </button>
        @endforeach
    </div>

    {{ $slot }}
</div>
