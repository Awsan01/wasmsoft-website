{{--
    Disclosure dropdown (button + panel). Used for menus of links/actions.
    Keyboard: Enter/Space toggles, Escape closes and returns focus, Tab moves
    through items; clicking outside closes. Behaviour lives in
    resources/js/modules/dropdown.js.

    Slots: `trigger` (button content), default slot (panel content).
--}}
@props([
    'id',
    'align' => 'end',
    'width' => 'w-56',
    'triggerClass' => '',
])

<div class="relative" data-dropdown>
    <button
        type="button"
        aria-expanded="false"
        aria-controls="{{ $id }}"
        data-dropdown-trigger
        @class(['inline-flex items-center gap-2 rounded-md transition-base', $triggerClass])
    >
        {{ $trigger }}
    </button>

    <div
        id="{{ $id }}"
        hidden
        data-dropdown-panel
        {{ $attributes->class([
            'absolute top-full z-(--z-dropdown) mt-2 rounded-lg border border-border bg-surface p-1.5 shadow-lg',
            $width,
            $align === 'start' ? 'inset-s-0' : 'inset-e-0',
        ]) }}
    >
        {{ $slot }}
    </div>
</div>
