@php
    $isNew = ! $page->exists;
    $canEdit = $isNew ? Gate::allows('create', \App\Models\Page::class) : Gate::allows('update', $page);
    $title = match (true) {
        $isNew => __('admin.pages.new'),
        $page->isSystem() => $page->system_key->label(),
        default => $page->displayTranslation()?->title ?? __('admin.pages.untitled'),
    };

    $fields = [
        'title' => ['required' => true, 'maxlength' => 200],
        ...($page->isSystem() ? [] : ['slug' => ['type' => 'slug', 'maxlength' => 120]]),
        'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.pages.summary_hint')],
        'body' => ['type' => 'markdown', 'rows' => 14],
        'meta_title' => ['seo' => true, 'maxlength' => 70],
        'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
    ];
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.pages.title'), 'url' => route('admin.pages.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" :description="$page->isSystem() ? __('admin.pages.system_edit_description') : null" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route('admin.pages.store') : route('admin.pages.update', $page) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <x-admin.section :title="__('admin.translations.label')">
                <x-admin.translation-editor :fields="$fields" :content="$page" />
            </x-admin.section>

            <div class="grid content-start gap-6">
                <x-admin.publishing :content="$page" :locked="$page->isSystem()">
                    <x-admin.view-links :content="$page" preview-route="admin.pages.preview" />
                </x-admin.publishing>

                @unless ($page->isSystem())
                    <x-admin.section :title="__('admin.fields.template')">
                        <x-ui.select
                            name="template"
                            :label="__('admin.fields.template')"
                            :options="\App\Content\PageTemplate::options()"
                            :value="$page->template"
                            :hint="__('admin.pages.template_hint')"
                            label-hidden
                            required
                        />
                    </x-admin.section>
                @endunless
            </div>
        </fieldset>
    </form>

    @if (! $isNew && Gate::allows('delete', $page))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.content.danger_text')">
            <x-admin.delete-button :action="route('admin.pages.destroy', $page)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
