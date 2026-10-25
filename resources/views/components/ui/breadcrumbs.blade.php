{{--
    Breadcrumb trail. `items` is a list of ['label' => string, 'url' => ?string];
    the last item is the current page. Separators mirror in RTL.
--}}
@props(['items' => []])

@if (count($items) > 0)
    <nav aria-label="{{ __('ui.breadcrumb') }}" {{ $attributes }}>
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm text-muted">
            @foreach ($items as $item)
                <li class="flex min-w-0 items-center gap-1.5">
                    @unless ($loop->first)
                        <x-ui.icon name="chevron-right" :size="14" class="text-border-strong" />
                    @endunless
                    @if (! $loop->last && ! empty($item['url']))
                        <a href="{{ $item['url'] }}" class="truncate rounded-xs transition-base hover:text-primary hover:underline">{{ $item['label'] }}</a>
                    @else
                        <span aria-current="page" class="truncate font-medium text-ink">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
