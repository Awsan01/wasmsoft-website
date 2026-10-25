{{-- Length-aware pagination (registered as the default view in AppServiceProvider). --}}
@if ($paginator->hasPages())
    @php
        $item = 'inline-grid h-10 min-w-10 place-items-center rounded-md px-3 text-sm font-semibold transition-base';
        $link = $item.' text-ink hover:bg-lavender hover:text-primary';
        $disabled = $item.' text-border-strong';
    @endphp

    <nav aria-label="{{ __('ui.pagination.label') }}" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="text-sm text-muted">
            {{ __('ui.pagination.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </p>

        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabled }}" aria-disabled="true">
                        <x-ui.icon name="chevron-left" :size="18" />
                        <span class="sr-only">{{ __('ui.pagination.previous') }}</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }}">
                        <x-ui.icon name="chevron-left" :size="18" />
                        <span class="sr-only">{{ __('ui.pagination.previous') }}</span>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $disabled }}" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $item }} bg-primary text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $link }}" aria-label="{{ __('ui.pagination.page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }}">
                        <x-ui.icon name="chevron-right" :size="18" />
                        <span class="sr-only">{{ __('ui.pagination.next') }}</span>
                    </a>
                @else
                    <span class="{{ $disabled }}" aria-disabled="true">
                        <x-ui.icon name="chevron-right" :size="18" />
                        <span class="sr-only">{{ __('ui.pagination.next') }}</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
