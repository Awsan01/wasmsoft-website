@php
    $isNew = ! $post->exists;
    $canEdit = $isNew ? Gate::allows('create', \App\Models\Post::class) : Gate::allows('update', $post);
    $canDelete = ! $isNew && Gate::allows('delete', $post);
    $title = $isNew ? __('admin.posts.new') : ($post->displayTranslation()?->title ?? __('admin.posts.untitled'));
    $user = auth()->user();
    // Authors keep their own article once an editor has published or archived it.
    $lockedOwnPost = ! $canEdit && ! $isNew && $user !== null && $post->isWrittenBy($user);

    $fields = [
        'title' => ['required' => true, 'maxlength' => 200],
        'slug' => ['type' => 'slug', 'maxlength' => 120],
        'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.posts.summary_hint')],
        'body' => ['type' => 'markdown', 'rows' => 18, 'hint' => __('admin.posts.body_hint')],
        'meta_title' => ['seo' => true, 'maxlength' => 70],
        'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
    ];
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.posts.title'), 'url' => route('admin.posts.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ $lockedOwnPost ? __('admin.posts.locked_own') : __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid min-w-0 content-start gap-6">
                <x-admin.section :title="__('admin.translations.label')">
                    <x-admin.translation-editor :fields="$fields" :content="$post" />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                @if ($canPublish)
                    <x-admin.publishing :content="$post" with-date>
                        <x-ui.checkbox name="is_featured" :label="__('admin.posts.featured_label')" :checked="$post->is_featured" />
                        <x-admin.view-links :content="$post" preview-route="admin.posts.preview" />
                    </x-admin.publishing>
                @else
                    <x-admin.publishing :content="$post" :statuses="[\App\Content\PublicationStatus::Draft->value => \App\Content\PublicationStatus::Draft->label()]">
                        <p class="text-sm text-muted">{{ __('admin.posts.author_note') }}</p>
                        <x-admin.view-links :content="$post" preview-route="admin.posts.preview" />
                    </x-admin.publishing>
                @endif

                <x-admin.section :title="__('admin.posts.details')">
                    @if ($canPublish)
                        <x-ui.select
                            name="author_id"
                            :label="__('admin.fields.author')"
                            :value="$post->author_id"
                            :placeholder="__('admin.posts.no_author')"
                            :options="$authors"
                            :hint="__('admin.posts.author_hint')"
                        />
                    @else
                        <div class="grid gap-1">
                            <span class="text-sm font-semibold text-ink">{{ __('admin.fields.author') }}</span>
                            <span class="text-sm text-muted">{{ $post->author?->name ?? $user?->name }}</span>
                        </div>
                    @endif

                    <x-ui.select
                        name="post_category_id"
                        :label="__('admin.fields.category')"
                        :value="$post->post_category_id"
                        :placeholder="__('admin.posts.no_category')"
                        :options="$categories"
                    />

                    <x-admin.checkbox-list
                        name="tags"
                        :label="__('admin.fields.tags')"
                        :options="$tags"
                        :selected="$selectedTags"
                        :hint="$tags === [] ? null : __('admin.posts.tags_hint', ['max' => \App\Http\Requests\Admin\PostRequest::MAX_TAGS])"
                        :empty="__('admin.posts.no_tags')"
                    />
                </x-admin.section>

                <x-admin.section :title="__('admin.fields.cover')">
                    <x-admin.media-picker name="cover_id" :label="__('admin.fields.cover')" :media="$post->cover" :hint="__('admin.posts.cover_hint')" label-hidden />
                </x-admin.section>
            </div>
        </fieldset>
    </form>

    @if ($canDelete)
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.content.danger_text')">
            <x-admin.delete-button :action="route('admin.posts.destroy', $post)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
