<x-layouts.admin :title="__('admin.projects.title')" :breadcrumbs="[['label' => __('admin.projects.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.projects.title')" :description="__('admin.projects.description')">
        <x-slot:actions>
            <x-ui.button :href="route('admin.project-categories.index')" variant="outline" icon="folder">{{ __('admin.project_categories.title') }}</x-ui.button>
            @can('create', \App\Models\Project::class)
                <x-ui.button :href="route('admin.projects.create')" icon="plus">{{ __('admin.projects.new') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.projects.index')" :search="$search" :active="$search !== '' || $status !== null || $categoryId !== null">
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :value="$status"
            :placeholder="__('admin.content.all_statuses')"
            :options="\App\Content\PublicationStatus::options()"
            wrapper-class="sm:w-48"
        />
        @if ($categories !== [])
            <x-ui.select
                name="category"
                :label="__('admin.fields.category')"
                :value="$categoryId"
                :placeholder="__('admin.projects.all_categories')"
                :options="$categories"
                wrapper-class="sm:w-56"
            />
        @endif
    </x-admin.filters>

    @if ($projects->isEmpty())
        @php
            $filtered = $search !== '' || $status !== null || $categoryId !== null;
        @endphp
        <x-ui.empty-state :title="$filtered ? __('admin.content.no_results') : __('admin.projects.empty_title')" icon="briefcase">
            @unless ($filtered)
                {{ __('admin.projects.empty_text') }}
                @can('create', \App\Models\Project::class)
                    <x-slot:actions>
                        <x-ui.button :href="route('admin.projects.create')" size="sm" icon="plus">{{ __('admin.projects.new') }}</x-ui.button>
                    </x-slot:actions>
                @endcan
            @endunless
        </x-ui.empty-state>
    @else
        <x-ui.table :caption="__('admin.projects.title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.fields.title') }}</th>
                    <th scope="col">{{ __('admin.fields.category') }}</th>
                    <th scope="col">{{ __('admin.fields.status') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.pages.columns.updated') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    @php
                        $display = $project->displayTranslation();
                        $name = $display?->title ?? __('admin.projects.untitled');
                        $category = $project->category?->displayTranslation();
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-md border border-border bg-canvas">
                                    @if ($project->cover)
                                        <img src="{{ $project->cover->urlForWidth(480) }}" alt="" loading="lazy" class="size-full object-cover">
                                    @else
                                        <x-ui.icon name="briefcase" :size="18" class="text-muted" />
                                    @endif
                                </span>
                                <div class="grid gap-1">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $name }}</a>
                                    @if ($project->is_featured)
                                        <x-ui.badge tone="primary" icon="star" class="justify-self-start">{{ __('admin.services.featured') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" @if ($category) lang="{{ $category->locale }}" @endif>{{ $category?->name ?? '—' }}</td>
                        <td><x-admin.status-badge :content="$project" /></td>
                        <td><x-admin.locale-chips :content="$project" /></td>
                        <td class="text-muted"><x-ui.time :value="$project->updated_at" format="relative" /></td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.projects.edit', $project)" variant="outline" size="sm" icon="pencil">
                                {{ __('admin.actions.edit') }}<span class="sr-only">: {{ $name }}</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        {{ $projects->links() }}
    @endif
</x-layouts.admin>
