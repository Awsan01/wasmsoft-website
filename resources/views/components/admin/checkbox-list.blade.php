{{--
    A group of checkboxes submitted as `{name}[]` (e.g. the tags of a post).
    `selected` holds the checked values; the controller resolves old input,
    so that unchecking everything survives a failed submission.
--}}
@props([
    'name',
    'label',
    'options' => [],
    'selected' => [],
    'hint' => null,
    'empty' => null,
])

@php
    $id = \App\View\FormField::id($name);
    $errorBag = view()->shared('errors');
    $error = \App\View\FormField::error($name);

    if ($error === null && $errorBag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = collect($errorBag->get($name.'.*'))->flatten()->first();
    }

    $checked = array_map('strval', $selected);
    $describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error);
@endphp

<fieldset {{ $attributes->class(['grid min-w-0 gap-2']) }} @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
    <legend class="mb-2 text-sm font-semibold text-ink">{{ $label }}</legend>

    @if ($options === [])
        <p class="text-sm text-muted">{{ $empty }}</p>
    @else
        <ul class="grid max-h-64 gap-2.5 overflow-y-auto rounded-lg border border-border bg-surface p-3">
            @foreach ($options as $value => $optionLabel)
                <li class="flex items-start gap-3">
                    <input
                        type="checkbox"
                        id="{{ $id }}-{{ $value }}"
                        name="{{ $name }}[]"
                        value="{{ $value }}"
                        @checked(in_array((string) $value, $checked, true))
                        @if ($error) aria-invalid="true" @endif
                        class="mt-0.5 size-5 shrink-0 cursor-pointer rounded-xs accent-primary"
                    >
                    <label for="{{ $id }}-{{ $value }}" class="cursor-pointer text-sm text-ink">{{ $optionLabel }}</label>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</fieldset>
