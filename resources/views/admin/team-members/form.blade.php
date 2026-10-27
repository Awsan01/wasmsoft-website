@php
    $isNew = ! $block->exists;
    $canEdit = $isNew ? Gate::allows('create', $modelClass) : Gate::allows('update', $block);
    $title = $isNew ? __($texts.'.new') : ($block->blockLabel() ?? __($texts.'.untitled'));
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __($texts.'.title'), 'url' => route($routes.'.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" :description="__('admin.team.consent_note')" />

    @unless ($canEdit)
        <x-ui.alert tone="info">{{ __('admin.content.read_only') }}</x-ui.alert>
    @endunless

    <form method="POST" action="{{ $isNew ? route($routes.'.store') : route($routes.'.update', $block) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <fieldset @disabled(! $canEdit) class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid min-w-0 content-start gap-6">
                <x-admin.section :title="__('admin.team.person')" :description="__('admin.blocks.text_hint')">
                    <x-ui.input name="name" :label="__('admin.fields.name')" :value="$block->name" maxlength="120" required wrapper-class="md:w-1/2" />
                    <x-admin.localized-input name="role" :label="__('admin.team.fields.role')" :values="$block->role ?? []" maxlength="120" />
                    <x-admin.localized-input name="bio" type="textarea" :label="__('admin.team.fields.bio')" :values="$block->bio ?? []" maxlength="600" :rows="3" />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    <x-ui.checkbox name="is_visible" :label="__('admin.blocks.visible_label')" :checked="$block->is_visible" />
                    <x-ui.button type="submit" icon="check" block>{{ $isNew ? __('admin.actions.create') : __('admin.actions.save') }}</x-ui.button>
                </x-admin.section>

                <x-admin.section :title="__('admin.team.fields.photo')">
                    <x-admin.media-picker name="photo_id" :label="__('admin.team.fields.photo')" :media="$block->photo" label-hidden />
                </x-admin.section>
            </div>
        </fieldset>
    </form>

    @if (! $isNew && Gate::allows('delete', $block))
        <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.blocks.delete_text')">
            <x-admin.delete-button :action="route($routes.'.destroy', $block)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
