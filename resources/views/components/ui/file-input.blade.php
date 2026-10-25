{{--
    File field with a translated button and the chosen file names, instead
    of the browser's own control (whose text follows the browser's language,
    not the page's). The native input stays focusable for keyboards and
    screen readers; resources/js/modules/file-input.js lists the chosen files.
--}}
@props([
    'name',
    'label',
    'hint' => null,
    'id' => null,
    'multiple' => false,
    'required' => false,
])

@php
    $id ??= \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
@endphp

<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required">
    <div class="flex flex-wrap items-center gap-3" data-file-input data-empty-label="{{ __('ui.no_file_chosen') }}">
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($multiple) multiple @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error)) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class(['peer sr-only']) }}
            data-file-input-control
        >
        <label for="{{ $id }}" class="inline-flex h-control-md cursor-pointer items-center gap-2 rounded-md bg-lavender px-4 text-sm font-semibold text-primary transition-base hover:bg-lavender-strong peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-secondary">
            <x-ui.icon name="folder" :size="18" />
            {{ $multiple ? __('ui.choose_files') : __('ui.choose_file') }}
        </label>
        <span class="min-w-0 text-sm text-muted" data-file-names aria-live="polite">{{ __('ui.no_file_chosen') }}</span>
    </div>
</x-ui.field>
