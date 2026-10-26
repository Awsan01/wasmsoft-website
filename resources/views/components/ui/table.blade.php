{{--
    Responsive data table: scrolls horizontally inside its card on narrow
    screens instead of overflowing the page. Provide a caption for screen
    readers; pass <thead>/<tbody> in the slot.

    The card is `relative` so screen-reader-only text in the cells (which is
    absolutely positioned) scrolls with the table instead of widening the page.
--}}
@props(['caption' => null])

<div {{ $attributes->class(['relative overflow-x-auto rounded-xl border border-border bg-surface shadow-xs']) }}>
    <table class="data-table">
        @if ($caption)
            <caption class="sr-only">{{ $caption }}</caption>
        @endif
        {{ $slot }}
    </table>
</div>
