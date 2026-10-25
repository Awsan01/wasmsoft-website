{{--
    Image field backed by the media library: a preview, "choose" (opens the
    picker dialog, where images can also be uploaded) and "remove". The
    selected media id is submitted in a hidden input named `name`.
--}}
@props([
    'name',
    'label',
    'media' => null,
    'hint' => null,
])

@php
    $id = \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $value = \App\View\FormField::old($name, $media?->id);
@endphp

<div
    {{ $attributes->class(['grid gap-1.5']) }}
    data-media-picker
    data-picker-url="{{ route('admin.media.picker') }}"
    data-empty-label="{{ __('admin.media.picker.none') }}"
    role="group"
    aria-labelledby="{{ $id }}-label"
>
    <span id="{{ $id }}-label" class="text-sm font-semibold text-ink">{{ $label }}</span>

    <div class="flex items-center gap-4">
        <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-border bg-canvas">
            <img data-media-preview src="{{ $media?->urlForWidth(480) }}" alt="" class="size-full object-cover" @if (! $media) hidden @endif>
            <x-ui.icon name="image" :size="24" class="text-muted" data-media-placeholder :hidden="(bool) $media" />
        </span>

        <div class="grid min-w-0 gap-2">
            <p class="truncate text-sm text-muted" data-media-name aria-live="polite">{{ $media?->original_name ?? __('admin.media.picker.none') }}</p>
            <div class="flex flex-wrap gap-2">
                <x-ui.button size="sm" variant="outline" icon="image" data-media-choose>{{ __('admin.media.picker.choose') }}</x-ui.button>
                <x-ui.button size="sm" variant="ghost" icon="x" data-media-remove :hidden="! $media">{{ __('admin.media.picker.remove') }}</x-ui.button>
            </div>
        </div>
    </div>

    <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" data-media-input>

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>

@pushOnce('dialogs')
    <x-ui.dialog id="media-picker-dialog" :title="__('admin.media.picker.title')" size="xl">
        <div data-media-picker-body aria-live="polite" class="min-h-48">
            <x-ui.spinner :label="__('ui.loading')" class="text-primary" />
        </div>
    </x-ui.dialog>
@endPushOnce
