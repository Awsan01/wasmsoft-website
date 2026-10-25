<x-layouts.admin :title="__('admin.pages.title')" :breadcrumbs="[['label' => __('admin.pages.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.pages.title')" :description="__('admin.pages.description')">
        @can('create', \App\Models\Page::class)
            <x-slot:actions>
                <x-ui.button :href="route('admin.pages.create')" icon="plus">{{ __('admin.pages.new') }}</x-ui.button>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    <section aria-labelledby="system-pages-title" class="grid gap-3">
        <div class="grid gap-1">
            <h2 id="system-pages-title" class="text-h3">{{ __('admin.pages.system_title') }}</h2>
            <p class="text-sm text-muted">{{ __('admin.pages.system_description') }}</p>
        </div>

        <x-ui.table :caption="__('admin.pages.system_title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.pages.columns.page') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.pages.columns.updated') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($systemPages as $page)
                    <tr>
                        <td class="font-semibold">{{ $page->system_key?->label() }}</td>
                        <td><x-admin.locale-chips :content="$page" /></td>
                        <td class="text-muted"><x-ui.time :value="$page->updated_at" format="relative" /></td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.pages.edit', $page)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: {{ $page->system_key?->label() }}</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </section>

    <section aria-labelledby="custom-pages-title" class="grid gap-3">
        <h2 id="custom-pages-title" class="text-h3">{{ __('admin.pages.custom_title') }}</h2>

        <x-admin.filters :action="route('admin.pages.index')" :search="$search" :active="$search !== '' || $status !== null">
            <x-ui.select
                name="status"
                :label="__('admin.fields.status')"
                :value="$status"
                :placeholder="__('admin.content.all_statuses')"
                :options="\App\Content\PublicationStatus::options()"
                wrapper-class="sm:w-48"
            />
        </x-admin.filters>

        @if ($pages->isEmpty())
            <x-ui.empty-state :title="$search !== '' || $status !== null ? __('admin.content.no_results') : __('admin.pages.empty_title')" icon="file-text">
                @if ($search === '' && $status === null)
                    {{ __('admin.pages.empty_text') }}
                    @can('create', \App\Models\Page::class)
                        <x-slot:actions>
                            <x-ui.button :href="route('admin.pages.create')" size="sm" icon="plus">{{ __('admin.pages.new') }}</x-ui.button>
                        </x-slot:actions>
                    @endcan
                @endif
            </x-ui.empty-state>
        @else
            <x-ui.table :caption="__('admin.pages.custom_title')">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.fields.title') }}</th>
                        <th scope="col">{{ __('admin.fields.template') }}</th>
                        <th scope="col">{{ __('admin.fields.status') }}</th>
                        <th scope="col">{{ __('admin.translations.label') }}</th>
                        <th scope="col">{{ __('admin.pages.columns.updated') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        @php
                            $display = $page->displayTranslation();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>
                                    {{ $display?->title ?? __('admin.pages.untitled') }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $page->template->label() }}</td>
                            <td><x-admin.status-badge :content="$page" /></td>
                            <td><x-admin.locale-chips :content="$page" /></td>
                            <td class="text-muted"><x-ui.time :value="$page->updated_at" format="relative" /></td>
                            <td class="text-end">
                                <x-ui.button :href="route('admin.pages.edit', $page)" variant="outline" size="sm" icon="pencil">
                                    {{ __('admin.actions.edit') }}<span class="sr-only">: {{ $display?->title }}</span>
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            {{ $pages->links() }}
        @endif
    </section>
</x-layouts.admin>
