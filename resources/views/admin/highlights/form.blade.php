@php
    $isNew = ! $block->exists;
    $canEdit = $isNew ? Gate::allows('create', $modelClass) : Gate::allows('update', $block);
    $title = $isNew ? __($texts.'.new') : ($block->blockLabel() ?? __($texts.'.untitled'));
    $listUrl = route($routes.'.index', ['group' => $block->group->value]);
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __($texts.'.title'), 'url' => $listUrl],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" />

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
                <x-admin.section :title="__('admin.blocks.text')" :description="__('admin.blocks.text_hint')">
                    <x-admin.localized-input name="title" :label="__('admin.fields.title')" :values="$block->title ?? []" maxlength="80" />
                    <x-admin.localized-input name="description" type="textarea" :label="__('admin.highlights.fields.description')" :values="$block->description ?? []" maxlength="300" :rows="3" />
                </x-admin.section>

                <x-admin.section :description="__('admin.highlights.icon_hint')">
                    <x-admin.icon-picker name="icon" :label="__('admin.fields.icon')" :value="$block->icon" />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    <x-ui.select
                        name="group"
                        :label="__('admin.highlights.fields.group')"
                        :options="collect(\App\Content\HighlightGroup::cases())->mapWithKeys(fn ($group) => [$group->value => $group->label()])->all()"
                        :value="$block->group->value"
                        required
                    />
                    <x-ui.input name="figure" :label="__('admin.highlights.fields.figure')" :value="$block->figure" :hint="__('admin.highlights.figure_hint')" maxlength="20" dir="ltr" />
                    <x-ui.checkbox name="is_visible" :label="__('admin.blocks.visible_label')" :checked="$block->is_visible" />
                    <x-ui.button type="submit" icon="check" block>{{ $isNew ? __('admin.actions.create') : __('admin.actions.save') }}</x-ui.button>
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
