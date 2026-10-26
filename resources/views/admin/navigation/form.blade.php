@php
    $isNew = ! $item->exists;
    $isGroup = $item->type === \App\Navigation\MenuLinkType::Group;
    $title = $isNew ? __($isGroup ? 'admin.navigation.new_group' : 'admin.navigation.new_link') : $item->adminLabel();
    $currentType = \App\View\FormField::old('type', $item->type->value);
    $linkTypes = [\App\Navigation\MenuLinkType::System, \App\Navigation\MenuLinkType::Page, \App\Navigation\MenuLinkType::Url];
@endphp

<x-layouts.admin
    :title="$title"
    :breadcrumbs="[
        ['label' => __('admin.navigation.title'), 'url' => route('admin.navigation.index')],
        ['label' => $title, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$title" :description="$item->menu->label()" />

    <form method="POST" action="{{ $isNew ? route('admin.navigation.store') : route('admin.navigation.update', $item) }}" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless
        <input type="hidden" name="menu" value="{{ $item->menu->value }}">

        <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid min-w-0 content-start gap-6">
                @if ($isGroup)
                    <input type="hidden" name="type" value="{{ \App\Navigation\MenuLinkType::Group->value }}">
                @else
                    <x-admin.section :title="__('admin.navigation.destination')">
                        <fieldset class="grid gap-3" @if ($typeError = \App\View\FormField::error('type')) aria-describedby="field-type-error" @endif>
                            <legend class="mb-1 text-sm font-semibold text-ink">{{ __('admin.navigation.fields.type') }}</legend>
                            <div class="grid gap-2 sm:grid-cols-3">
                                @foreach ($linkTypes as $type)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-border p-3 text-sm font-semibold text-ink transition-base hover:border-purple-300 has-checked:border-primary has-checked:bg-lavender">
                                        <input type="radio" name="type" value="{{ $type->value }}" @checked($currentType === $type->value) class="size-4 accent-primary">
                                        {{ $type->label() }}
                                    </label>
                                @endforeach
                            </div>
                            @if ($typeError)
                                <p id="field-type-error" class="text-sm font-medium text-error">{{ $typeError }}</p>
                            @endif
                        </fieldset>

                        <div data-show-group="type" data-show-for="system">
                            <x-ui.select name="system_page" :label="__('admin.navigation.fields.system_page')" :options="$systemPages" :value="$item->system_page?->value" />
                        </div>

                        <div data-show-group="type" data-show-for="page">
                            <x-ui.select
                                name="page_id"
                                :label="__('admin.navigation.fields.page_id')"
                                :options="$pages"
                                :value="$item->page_id"
                                :placeholder="__('admin.navigation.choose_page')"
                                :hint="__('admin.navigation.page_hint')"
                            />
                        </div>

                        <div class="grid gap-4" data-show-group="type" data-show-for="url">
                            <x-ui.input type="url" name="url" :label="__('admin.navigation.fields.url')" :value="$item->url" :hint="__('admin.navigation.url_hint')" placeholder="https://" maxlength="500" dir="ltr" />
                            <x-ui.checkbox name="opens_in_new_tab" :label="__('admin.navigation.fields.opens_in_new_tab')" :checked="$item->opens_in_new_tab" />
                        </div>
                    </x-admin.section>
                @endif

                <x-admin.section :title="__('admin.navigation.fields.label')">
                    <x-admin.localized-input
                        name="label"
                        :label="__('admin.navigation.fields.label')"
                        :values="$item->label ?? []"
                        :hint="$isGroup ? __('admin.navigation.label_hint_required') : __('admin.navigation.label_hint')"
                        maxlength="60"
                        label-hidden
                    />
                </x-admin.section>
            </div>

            <div class="grid content-start gap-6">
                <x-admin.section>
                    @if ($item->menu === \App\Navigation\MenuLocation::Footer && ! $isGroup)
                        <x-ui.select name="parent_id" :label="__('admin.navigation.fields.parent_id')" :options="$groups" :value="$item->parent_id" :placeholder="__('admin.navigation.choose_group')" required />
                    @endif
                    <x-ui.checkbox name="is_visible" :label="__('admin.blocks.visible_label')" :checked="$item->is_visible" />
                    <x-ui.button type="submit" icon="check" block>{{ $isNew ? __('admin.actions.create') : __('admin.actions.save') }}</x-ui.button>
                </x-admin.section>
            </div>
        </div>
    </form>

    @if (! $isNew && Gate::allows('delete', $item))
        <x-admin.section :title="__('admin.content.danger_title')" :description="$isGroup ? __('admin.navigation.delete_group_text') : __('admin.navigation.delete_text')">
            <x-admin.delete-button :action="route('admin.navigation.destroy', $item)" :name="$title" class="justify-self-start" />
        </x-admin.section>
    @endif
</x-layouts.admin>
