<x-layouts.admin :title="__('admin.posts.title')" :breadcrumbs="[['label' => __('admin.posts.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.posts.title')" :description="__('admin.posts.description')">
        <x-slot:actions>
            <x-ui.button :href="route('admin.post-categories.index')" variant="outline" icon="folder">{{ __('admin.post_categories.title') }}</x-ui.button>
            <x-ui.button :href="route('admin.tags.index')" variant="outline" icon="tag">{{ __('admin.tags.title') }}</x-ui.button>
            @can('create', \App\Models\Post::class)
                <x-ui.button :href="route('admin.posts.create')" icon="plus">{{ __('admin.posts.new') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @php
        $filtered = $search !== '' || $status !== null || $categoryId !== null || $authorId !== null;
    @endphp

    <x-admin.filters :action="route('admin.posts.index')" :search="$search" :active="$filtered">
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :value="$status"
            :placeholder="__('admin.content.all_statuses')"
            :options="$statuses"
            wrapper-class="sm:w-48"
        />
        @if ($categories !== [])
            <x-ui.select
                name="category"
                :label="__('admin.fields.category')"
                :value="$categoryId"
                :placeholder="__('admin.posts.all_categories')"
                :options="$categories"
                wrapper-class="sm:w-56"
            />
        @endif
        @if (count($authors) > 1)
            <x-ui.select
                name="author"
                :label="__('admin.fields.author')"
                :value="$authorId"
                :placeholder="__('admin.posts.all_authors')"
                :options="$authors"
                wrapper-class="sm:w-56"
            />
        @endif
    </x-admin.filters>

    @if ($posts->isEmpty())
        <x-ui.empty-state :title="$filtered ? __('admin.content.no_results') : __('admin.posts.empty_title')" icon="newspaper">
            @unless ($filtered)
                {{ __('admin.posts.empty_text') }}
                @can('create', \App\Models\Post::class)
                    <x-slot:actions>
                        <x-ui.button :href="route('admin.posts.create')" size="sm" icon="plus">{{ __('admin.posts.new') }}</x-ui.button>
                    </x-slot:actions>
                @endcan
            @endunless
        </x-ui.empty-state>
    @else
        <x-ui.table :caption="__('admin.posts.title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.fields.title') }}</th>
                    <th scope="col">{{ __('admin.fields.category') }}</th>
                    <th scope="col">{{ __('admin.fields.author') }}</th>
                    <th scope="col">{{ __('admin.fields.status') }}</th>
                    <th scope="col">{{ __('admin.translations.label') }}</th>
                    <th scope="col">{{ __('admin.fields.published_at') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post)
                    @php
                        $display = $post->displayTranslation();
                        $name = $display?->title ?? __('admin.posts.untitled');
                        $category = $post->category?->displayTranslation();
                        $canUpdate = Gate::allows('update', $post);
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-md border border-border bg-canvas">
                                    @if ($post->cover)
                                        <img src="{{ $post->cover->urlForWidth(480) }}" alt="" loading="lazy" class="size-full object-cover">
                                    @else
                                        <x-ui.icon name="newspaper" :size="18" class="text-muted" />
                                    @endif
                                </span>
                                <div class="grid gap-1">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $name }}</a>
                                    @if ($post->is_featured)
                                        <x-ui.badge tone="primary" icon="star" class="justify-self-start">{{ __('admin.services.featured') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" @if ($category) lang="{{ $category->locale }}" @endif>{{ $category?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $post->author?->name ?? '—' }}</td>
                        <td><x-admin.status-badge :content="$post" /></td>
                        <td><x-admin.locale-chips :content="$post" /></td>
                        <td class="whitespace-nowrap text-muted">
                            @if ($post->published_at)
                                <x-ui.time :value="$post->published_at" format="datetime" style="short" />
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.posts.edit', $post)" variant="outline" size="sm" :icon="$canUpdate ? 'pencil' : 'eye'">
                                {{ $canUpdate ? __('admin.actions.edit') : __('admin.actions.view') }}<span class="sr-only">: {{ $name }}</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        {{ $posts->links() }}
    @endif
</x-layouts.admin>
