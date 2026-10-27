<x-layouts.admin :title="__('admin.navigation.title')" :breadcrumbs="[['label' => __('admin.navigation.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.navigation.title')" :description="__('admin.navigation.description')" />

    <x-admin.section :title="__('admin.navigation.menus.header')" :description="__('admin.navigation.menu_hints.header')">
        @if ($header->isEmpty())
            <p class="text-sm text-muted">{{ __('admin.navigation.empty_menu') }}</p>
        @else
            @include('admin.navigation.partials.items', ['items' => $header, 'caption' => __('admin.navigation.menus.header')])
        @endif

        <div>
            <x-ui.button :href="route('admin.navigation.create', ['menu' => 'header'])" variant="outline" size="sm" icon="plus">{{ __('admin.navigation.add_link') }}</x-ui.button>
        </div>
    </x-admin.section>

    <x-admin.section :title="__('admin.navigation.menus.footer')" :description="__('admin.navigation.menu_hints.footer')">
        @foreach ($footer as $group)
            @php
                $groupLabel = $group->adminLabel();
            @endphp
            <section class="grid gap-3 rounded-lg border border-border p-4" aria-labelledby="footer-group-{{ $group->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($canReorder)
                            <x-admin.move-buttons :url="route('admin.navigation.move', $group)" :label="$groupLabel" :first="$loop->first" :last="$loop->last" />
                        @endif
                        <h3 id="footer-group-{{ $group->id }}" class="text-base font-semibold text-ink"><bdi>{{ $groupLabel }}</bdi></h3>
                        @unless ($group->is_visible)
                            <x-ui.badge tone="neutral" dot>{{ __('admin.blocks.hidden') }}</x-ui.badge>
                        @endunless
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button :href="route('admin.navigation.edit', $group)" variant="ghost" size="sm" icon="pencil">
                            {{ __('admin.actions.edit') }}<span class="sr-only">: <bdi>{{ $groupLabel }}</bdi></span>
                        </x-ui.button>
                        <x-ui.button :href="route('admin.navigation.create', ['menu' => 'footer', 'parent' => $group->id])" variant="outline" size="sm" icon="plus">
                            {{ __('admin.navigation.add_link') }}<span class="sr-only">: <bdi>{{ $groupLabel }}</bdi></span>
                        </x-ui.button>
                    </div>
                </div>

                @if ($group->children->isEmpty())
                    <p class="text-sm text-muted">{{ __('admin.navigation.empty_group') }}</p>
                @else
                    @include('admin.navigation.partials.items', ['items' => $group->children, 'caption' => $groupLabel])
                @endif
            </section>
        @endforeach

        <div>
            <x-ui.button :href="route('admin.navigation.create', ['menu' => 'footer', 'type' => 'group'])" variant="outline" size="sm" icon="plus">{{ __('admin.navigation.add_group') }}</x-ui.button>
        </div>
    </x-admin.section>
</x-layouts.admin>
