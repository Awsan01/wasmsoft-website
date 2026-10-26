{{--
    Shared list of home and about page items (highlights, testimonials,
    team members, milestones). `tabs` optionally splits the list (e.g.
    highlight groups): list of [label, url, active].
--}}
@php
    $tabs ??= [];
    $createUrl = route($routes.'.create', $createParameters ?? []);
@endphp

<x-layouts.admin :title="__($texts.'.title')" :breadcrumbs="[['label' => __($texts.'.title'), 'url' => null]]">
    <x-admin.page-header :title="__($texts.'.title')" :description="$description ?? __($texts.'.description')">
        @can('create', $modelClass)
            <x-slot:actions>
                <x-ui.button :href="$createUrl" icon="plus">{{ __($texts.'.new') }}</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    @if ($tabs !== [])
        <nav aria-label="{{ __($texts.'.groups_label') }}">
            <ul class="flex flex-wrap gap-2">
                @foreach ($tabs as [$label, $url, $active])
                    <li><x-site.filter-chip :href="$url" :current="$active">{{ $label }}</x-site.filter-chip></li>
                @endforeach
            </ul>
        </nav>
    @endif

    @if ($blocks->isEmpty())
        <x-ui.empty-state :title="__($texts.'.empty_title')" icon="sparkles">
            {{ __($texts.'.empty_text') }}
            @can('create', $modelClass)
                <x-slot:actions>
                    <x-ui.button :href="$createUrl" size="sm" icon="plus">{{ __($texts.'.new') }}</x-ui.button>
                </x-slot:actions>
            @endcan
        </x-ui.empty-state>
    @else
        @if ($canReorder)
            <p class="text-sm text-muted">{{ __('admin.content.reorder_hint') }}</p>
        @endif

        <x-ui.table :caption="__($texts.'.title')">
            <thead>
                <tr>
                    @if ($canReorder)
                        <th scope="col">{{ __('admin.content.order') }}</th>
                    @endif
                    <th scope="col">{{ __($texts.'.item') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.blocks.visibility') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blocks as $block)
                    @php
                        $label = $block->blockLabel() ?? __($texts.'.untitled');
                        $image = $block->blockImage();
                    @endphp
                    <tr>
                        @if ($canReorder)
                            <td><x-admin.move-buttons :url="route($routes.'.move', $block)" :label="$label" :first="$loop->first" :last="$loop->last" /></td>
                        @endif
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($image)
                                    <img src="{{ $image->urlForWidth(480) }}" alt="" loading="lazy" class="size-10 shrink-0 rounded-full border border-border object-cover">
                                @endif
                                <div class="grid min-w-0 gap-0.5">
                                    <a href="{{ route($routes.'.edit', $block) }}" class="font-semibold text-ink hover:text-primary hover:underline"><bdi>{{ $label }}</bdi></a>
                                    @if ($detail = $block->blockDetail())
                                        <span class="truncate text-sm text-muted"><bdi>{{ $detail }}</bdi></span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><x-admin.locale-chips :content="$block" /></td>
                        <td>
                            @if ($block->isVisible())
                                <x-ui.badge tone="success" dot>{{ __('admin.blocks.visible') }}</x-ui.badge>
                            @else
                                <x-ui.badge tone="neutral" dot>{{ __('admin.blocks.hidden') }}</x-ui.badge>
                            @endif
                        </td>
                        <td class="text-end">
                            <x-ui.button :href="route($routes.'.edit', $block)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: <bdi>{{ $label }}</bdi></span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
</x-layouts.admin>
