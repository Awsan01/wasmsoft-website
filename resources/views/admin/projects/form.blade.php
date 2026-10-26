@php
    $isNew = ! $project->exists;
    $canEdit = $isNew ? Gate::allows('create', \App\Models\Project::class) : Gate::allows('update', $project);
    $title = $isNew ? __('admin.projects.new') : ($project->displayTranslation()?->title ?? __('admin.projects.untitled'));

    $fields = [
        'title' => ['required' => true, 'maxlength' => 200],
        'slug' => ['type' => 'slug', 'maxlength' => 120],
        'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.services.summary_hint')],
        'body' => ['type' => 'markdown', 'rows' => 14, 'hint' => __('admin.projects.body_hint')],
        'meta_title' => ['seo' => true, 'maxlength' => 70],
        'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
    ];
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.projects.title'), 'url' => route('admin.projects.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route('admin.projects.store') : route('admin.projects.update', $project) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid min-w-0 content-start gap-6">
                <x-admin.section :title="__('admin.translations.label')">
                    <x-admin.translation-editor :fields="$fields" :content="$project" />
                </x-admin.section>

                <x-admin.section :title="__('admin.fields.gallery')" :description="__('admin.projects.gallery_hint', ['max' => \App\Http\Requests\Admin\ProjectRequest::MAX_GALLERY_IMAGES])">
                    <x-admin.gallery-picker name="gallery" :label="__('admin.fields.gallery')" :media="$gallery" :max="\App\Http\Requests\Admin\ProjectRequest::MAX_GALLERY_IMAGES" label-hidden />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.publishing :content="$project">
                    <x-ui.checkbox name="is_featured" :label="__('admin.services.featured_label')" :checked="$project->is_featured" />
                    <x-admin.view-links :content="$project" preview-route="admin.projects.preview" />
                </x-admin.publishing>

                <x-admin.section :title="__('admin.projects.details')">
                    <x-ui.select
                        name="project_category_id"
                        :label="__('admin.fields.category')"
                        :value="$project->project_category_id"
                        :placeholder="__('admin.projects.no_category')"
                        :options="$categories"
                    />
                    <x-ui.input name="client_name" :label="__('admin.fields.client_name')" :value="$project->client_name" :hint="__('admin.projects.client_hint')" maxlength="150" />
                    <x-ui.input type="url" name="project_url" :label="__('admin.fields.project_url')" :value="$project->project_url" placeholder="https://" maxlength="255" dir="ltr" />
                    <x-ui.input type="date" name="completed_on" :label="__('admin.fields.completed_on')" :value="$project->completed_on?->format('Y-m-d')" dir="ltr" />
                    <x-ui.input name="technologies" :label="__('admin.fields.technologies')" :value="$project->technologies" :hint="__('admin.projects.technologies_hint')" dir="ltr" />
                </x-admin.section>

                <x-admin.section :title="__('admin.fields.cover')">
                    <x-admin.media-picker name="cover_id" :label="__('admin.fields.cover')" :media="$project->cover" :hint="__('admin.projects.cover_hint')" label-hidden />
                </x-admin.section>
            </div>
        </fieldset>
    </form>

    @if (! $isNew && Gate::allows('delete', $project))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.content.danger_text')">
            <x-admin.delete-button :action="route('admin.projects.destroy', $project)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
