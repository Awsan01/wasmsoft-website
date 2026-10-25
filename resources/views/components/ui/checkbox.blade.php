{{--
    Checkbox with an inline label. A hidden input sends `0` when unchecked so
    boolean fields are always present in the request.
--}}
@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
    'hint' => null,
    'id' => null,
    'withHidden' => true,
])

@php
    $id ??= \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $old = \App\View\FormField::old($name);
    $isChecked = $old !== null ? (string) $old === (string) $value : (bool) $checked;
@endphp

<div {{ $attributes->only('class')->class(['grid gap-1']) }}>
    <div class="flex items-start gap-3">
        @if ($withHidden)
            <input type="hidden" name="{{ $name }}" value="0">
        @endif
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error)) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('class')->class(['mt-0.5 size-5 shrink-0 cursor-pointer rounded-xs accent-primary']) }}
        >
        <label for="{{ $id }}" class="cursor-pointer text-sm font-medium text-ink">{{ $label }}</label>
    </div>
    @if ($hint)
        <p id="{{ $id }}-hint" class="ps-8 text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 ps-8 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
