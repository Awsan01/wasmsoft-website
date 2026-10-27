{{-- Links of one menu or footer column, in display order. --}}
<x-ui.table :caption="$caption">
    <thead>
        <tr>
            @if ($canReorder)
                <th scope="col">{{ __('admin.content.order') }}</th>
            @endif
            <th scope="col">{{ __('admin.navigation.fields.label') }}</th>
            <th scope="col">{{ __('admin.navigation.destination') }}</th>
            <th scope="col">{{ __('admin.blocks.visibility') }}</th>
            <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $link)
            @php
                $label = $link->adminLabel();
                $destination = match ($link->type) {
                    \App\Navigation\MenuLinkType::System => $link->system_page?->label(),
                    \App\Navigation\MenuLinkType::Page => $link->page?->displayTranslation()?->title,
                    default => $link->url,
                };
            @endphp
            <tr>
                @if ($canReorder)
                    <td><x-admin.move-buttons :url="route('admin.navigation.move', $link)" :label="$label" :first="$loop->first" :last="$loop->last" /></td>
                @endif
                <td>
                    <a href="{{ route('admin.navigation.edit', $link) }}" class="font-semibold text-ink hover:text-primary hover:underline"><bdi>{{ $label }}</bdi></a>
                </td>
                <td>
                    <span class="grid gap-0.5">
                        <span class="text-xs text-muted">{{ $link->type->label() }}</span>
                        <span @class(['text-sm', 'break-all' => $link->type === \App\Navigation\MenuLinkType::Url]) @if ($link->type === \App\Navigation\MenuLinkType::Url) dir="ltr" @endif><bdi>{{ $destination ?? '—' }}</bdi></span>
                    </span>
                </td>
                <td>
                    @if ($link->is_visible)
                        <x-ui.badge tone="success" dot>{{ __('admin.blocks.visible') }}</x-ui.badge>
                    @else
                        <x-ui.badge tone="neutral" dot>{{ __('admin.blocks.hidden') }}</x-ui.badge>
                    @endif
                </td>
                <td class="text-end">
                    <x-ui.button :href="route('admin.navigation.edit', $link)" variant="outline" size="sm" icon="pencil">
                        {{ __('admin.actions.edit') }}<span class="sr-only">: <bdi>{{ $label }}</bdi></span>
                    </x-ui.button>
                </td>
            </tr>
        @endforeach
    </tbody>
</x-ui.table>
