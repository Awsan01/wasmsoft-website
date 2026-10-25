@php
    $isNew = ! $service->exists;
    $canEdit = $isNew ? Gate::allows('create', \App\Models\Service::class) : Gate::allows('update', $service);
    $title = $isNew ? __('admin.services.new') : ($service->displayTranslation()?->title ?? __('admin.services.untitled'));

    $fields = [
        'title' => ['required' => true, 'maxlength' => 200],
        'slug' => ['type' => 'slug', 'maxlength' => 120],
        'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.services.summary_hint')],
        'body' => ['type' => 'markdown', 'rows' => 12],
        'benefits' => ['type' => 'lines', 'rows' => 5, 'hint' => __('admin.services.benefits_hint')],
        'meta_title' => ['seo' => true, 'maxlength' => 70],
        'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
    ];
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.services.title'), 'url' => route('admin.services.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route('admin.services.store') : route('admin.services.update', $service) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <x-admin.section :title="__('admin.translations.label')">
                <x-admin.translation-editor :fields="$fields" :content="$service" />
            </x-admin.section>

            <div class="grid content-start gap-6">
                <x-admin.publishing :content="$service">
                    <x-ui.checkbox name="is_featured" :label="__('admin.services.featured_label')" :checked="$service->is_featured" />
                    <x-admin.view-links :content="$service" preview-route="admin.services.preview" />
                </x-admin.publishing>

                <x-admin.section :title="__('admin.services.appearance')">
                    <x-admin.media-picker name="image_id" :label="__('admin.fields.image')" :media="$service->image" :hint="__('admin.services.image_hint')" />
                    <x-admin.icon-picker name="icon" :label="__('admin.fields.icon')" :value="$service->icon" />
                </x-admin.section>
            </div>
        </fieldset>
    </form>

    @if (! $isNew && Gate::allows('delete', $service))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.content.danger_text')">
            <x-admin.delete-button :action="route('admin.services.destroy', $service)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
