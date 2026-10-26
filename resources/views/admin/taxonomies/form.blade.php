{{-- Shared form of a vocabulary term: translated name and slug. --}}
@php
    $isNew = ! $term->exists;
    $canEdit = $isNew ? Gate::allows('create', $modelClass) : Gate::allows('update', $term);
    $title = $isNew ? __($texts.'.new') : ($term->displayTranslation()?->name ?? __($texts.'.untitled'));
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => $parent['label'], 'url' => $parent['url']],
        ['label' => __($texts.'.title'), 'url' => route($routes.'.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route($routes.'.store') : route($routes.'.update', $term) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <x-admin.section :title="__('admin.translations.label')">
                <x-admin.translation-editor
                    :fields="['name' => ['required' => true, 'maxlength' => 100], 'slug' => ['type' => 'slug', 'maxlength' => 120]]"
                    :content="$term"
                />
            </x-admin.section>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    <x-ui.button type="submit" icon="check" block>{{ $isNew ? __('admin.actions.create') : __('admin.actions.save') }}</x-ui.button>
                </x-admin.section>
            </div>
        </fieldset>
    </form>

    @if (! $isNew && Gate::allows('delete', $term))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__($texts.'.delete_text')">
            <x-admin.delete-button :action="route($routes.'.destroy', $term)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
