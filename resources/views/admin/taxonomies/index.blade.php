{{-- Shared list of a vocabulary (project categories, news categories, tags). --}}
<x-layouts.admin
    :title="__($texts.'.title')"
    :breadcrumbs="[
        ['label' => $parent['label'], 'url' => $parent['url']],
        ['label' => __($texts.'.title'), 'url' => null],
    ]"
>
    <x-admin.page-header :title="__($texts.'.title')" :description="__($texts.'.description')">
        @can('create', $modelClass)
            <x-slot:actions>
                <x-ui.button :href="route($routes.'.create')" icon="plus">{{ __($texts.'.new') }}</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    @if ($terms->isEmpty())
        <x-ui.empty-state :title="__($texts.'.empty_title')" icon="tag">
            {{ __($texts.'.empty_text') }}
            @can('create', $modelClass)
                <x-slot:actions>
                    <x-ui.button :href="route($routes.'.create')" size="sm" icon="plus">{{ __($texts.'.new') }}</x-ui.button>
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
                    <th scope="col">{{ __('admin.fields.name') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.content.usage') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terms as $term)
                    @php
                        $display = $term->displayTranslation();
                        $name = $display?->name ?? __($texts.'.untitled');
                        $count = $term->{$usage.'_count'};
                    @endphp
                    <tr>
                        @if ($canReorder)
                            <td><x-admin.move-buttons :url="route($routes.'.move', $term)" :label="$name" :first="$loop->first" :last="$loop->last" /></td>
                        @endif
                        <td>
                            <a href="{{ route($routes.'.edit', $term) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $name }}</a>
                        </td>
                        <td><x-admin.locale-chips :content="$term" /></td>
                        <td class="text-muted">{{ trans_choice($texts.'.usage', $count, ['count' => app(\App\Localization\Formatter::class)->number($count)]) }}</td>
                        <td class="text-end">
                            <x-ui.button :href="route($routes.'.edit', $term)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: {{ $name }}</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
</x-layouts.admin>
