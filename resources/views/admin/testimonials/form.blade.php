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
                <x-admin.section :title="__('admin.testimonials.fields.quote')" :description="__('admin.testimonials.quote_hint')">
                    <x-admin.localized-input name="quote" type="textarea" :label="__('admin.testimonials.fields.quote')" :values="$block->quote ?? []" maxlength="600" :rows="4" label-hidden />
                </x-admin.section>

                <x-admin.section :title="__('admin.testimonials.person')">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.input name="author_name" :label="__('admin.testimonials.fields.author_name')" :value="$block->author_name" maxlength="120" required />
                        <x-ui.input name="company" :label="__('admin.testimonials.fields.company')" :value="$block->company" maxlength="150" />
                    </div>
                    <x-admin.localized-input name="author_role" :label="__('admin.testimonials.fields.author_role')" :values="$block->author_role ?? []" maxlength="120" />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    <x-ui.checkbox name="has_consent" :label="__('admin.testimonials.fields.has_consent')" :hint="__('admin.testimonials.consent_hint')" :checked="$block->has_consent" />
                    <x-ui.checkbox name="is_visible" :label="__('admin.blocks.visible_label')" :checked="$block->is_visible" />
                    <x-ui.button type="submit" icon="check" block>{{ $isNew ? __('admin.actions.create') : __('admin.actions.save') }}</x-ui.button>
                </x-admin.section>

                <x-admin.section :title="__('admin.testimonials.fields.photo')">
                    <x-admin.media-picker name="photo_id" :label="__('admin.testimonials.fields.photo')" :media="$block->photo" :hint="__('admin.testimonials.photo_hint')" label-hidden />
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
