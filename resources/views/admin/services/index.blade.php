<x-layouts.admin :title="__('admin.services.title')" :breadcrumbs="[['label' => __('admin.services.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.services.title')" :description="__('admin.services.description')">
        @can('create', \App\Models\Service::class)
            <x-slot:actions>
                <x-ui.button :href="route('admin.services.create')" icon="plus">{{ __('admin.services.new') }}</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.services.index')" :search="$search" :active="$search !== '' || $status !== null">
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :value="$status"
            :placeholder="__('admin.content.all_statuses')"
            :options="\App\Content\PublicationStatus::options()"
            wrapper-class="sm:w-48"
        />
    </x-admin.filters>

    @if ($services->isEmpty())
        <x-ui.empty-state :title="$search !== '' || $status !== null ? __('admin.content.no_results') : __('admin.services.empty_title')" icon="layers">
            @if ($search === '' && $status === null)
                {{ __('admin.services.empty_text') }}
                @can('create', \App\Models\Service::class)
                    <x-slot:actions>
                        <x-ui.button :href="route('admin.services.create')" size="sm" icon="plus">{{ __('admin.services.new') }}</x-ui.button>
                    </x-slot:actions>
                @endcan
            @endif
        </x-ui.empty-state>
    @else
        @if ($canReorder)
            <p class="text-sm text-muted">{{ __('admin.content.reorder_hint') }}</p>
        @endif

        <x-ui.table :caption="__('admin.services.title')">
            <thead>
                <tr>
                    @if ($canReorder)
                        <th scope="col">{{ __('admin.content.order') }}</th>
                    @endif
                    <th scope="col">{{ __('admin.fields.title') }}</th>
                    <th scope="col">{{ __('admin.fields.status') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.pages.columns.updated') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($services as $service)
                    @php
                        $display = $service->displayTranslation();
                        $name = $display?->title ?? __('admin.services.untitled');
                    @endphp
                    <tr>
                        @if ($canReorder)
                            <td>
                                <x-admin.move-buttons :url="route('admin.services.move', $service)" :label="$name" :first="$loop->first" :last="$loop->last" />
                            </td>
                        @endif
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($service->icon)
                                    <span class="notch-corner grid size-9 shrink-0 place-items-center rounded-md bg-lavender text-primary">
                                        <x-ui.icon :name="$service->icon->value" :size="18" />
                                    </span>
                                @endif
                                <div class="grid gap-1">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $name }}</a>
                                    @if ($service->is_featured)
                                        <x-ui.badge tone="primary" icon="star" class="justify-self-start">{{ __('admin.services.featured') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><x-admin.status-badge :content="$service" /></td>
                        <td><x-admin.locale-chips :content="$service" /></td>
                        <td class="text-muted"><x-ui.time :value="$service->updated_at" format="relative" /></td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.services.edit', $service)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: {{ $name }}</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
</x-layouts.admin>
