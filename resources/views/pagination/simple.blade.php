{{-- Previous / next pagination for simplePaginate() results. --}}
@if ($paginator->hasPages())
    <nav aria-label="{{ __('ui.pagination.label') }}" class="flex items-center justify-between gap-4">
        @if ($paginator->onFirstPage())
            <span></span>
        @else
            <x-ui.button :href="$paginator->previousPageUrl()" variant="outline" size="sm" icon="chevron-left" rel="prev">
                {{ __('ui.pagination.previous') }}
            </x-ui.button>
        @endif

        @if ($paginator->hasMorePages())
            <x-ui.button :href="$paginator->nextPageUrl()" variant="outline" size="sm" icon-end="chevron-right" rel="next">
                {{ __('ui.pagination.next') }}
            </x-ui.button>
        @endif
    </nav>
@endif
