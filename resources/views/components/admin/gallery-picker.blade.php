{{--
    Ordered set of images from the media library (e.g. a project gallery).
    "Add images" opens the shared picker dialog; each chosen image is
    submitted as `{name}[]` in display order and can be removed again.
    `label-hidden` works as on the media picker.
--}}
@props([
    'name',
    'label',
    'media',
    'hint' => null,
    'max' => 24,
    'labelHidden' => false,
])

@php
    $id = \App\View\FormField::id($name);
    $errorBag = view()->shared('errors');
    $error = \App\View\FormField::error($name);

    if ($error === null && $errorBag instanceof \Illuminate\Support\ViewErrorBag) {
        // Errors of single entries ("gallery.3") are shown once for the whole field.
        $error = collect($errorBag->get($name.'.*'))->flatten()->first();
    }

    $removeLabel = __('admin.media.picker.remove_named');
@endphp

<div
    {{ $attributes->class(['grid gap-2']) }}
    data-media-picker
    data-gallery
    data-gallery-max="{{ $max }}"
    data-picker-url="{{ route('admin.media.picker') }}"
    role="group"
    aria-labelledby="{{ $id }}-label"
>
    <span id="{{ $id }}-label" @class(['text-sm font-semibold text-ink', 'sr-only' => $labelHidden])>{{ $label }}</span>

    <ul class="grid grid-cols-3 gap-2" data-gallery-items>
        @foreach ($media as $item)
            <li class="relative" data-gallery-item>
                <img src="{{ $item->urlForWidth(480) }}" alt="" class="aspect-square w-full rounded-md border border-border object-cover">
                <input type="hidden" name="{{ $name }}[]" value="{{ $item->id }}">
                <button type="button" data-gallery-remove aria-label="{{ str_replace(':name', $item->original_name, $removeLabel) }}" class="absolute inset-e-1 top-1 grid size-7 place-items-center rounded-full bg-surface/90 text-ink shadow-sm transition-base hover:text-error">
                    <x-ui.icon name="x" :size="14" />
                </button>
            </li>
        @endforeach
    </ul>

    <template data-gallery-template>
        <li class="relative" data-gallery-item>
            <img src="" alt="" class="aspect-square w-full rounded-md border border-border object-cover">
            <input type="hidden" name="{{ $name }}[]" value="">
            <button type="button" data-gallery-remove data-label-template="{{ $removeLabel }}" class="absolute inset-e-1 top-1 grid size-7 place-items-center rounded-full bg-surface/90 text-ink shadow-sm transition-base hover:text-error">
                <x-ui.icon name="x" :size="14" />
            </button>
        </li>
    </template>

    <div>
        <x-ui.button size="sm" variant="outline" icon="plus" data-media-choose>{{ __('admin.media.picker.add') }}</x-ui.button>
    </div>

    @if ($hint)
        <p class="text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="flex items-start gap-1.5 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>

@pushOnce('dialogs', 'media-picker-dialog')
    <x-admin.media-picker-dialog />
@endPushOnce
