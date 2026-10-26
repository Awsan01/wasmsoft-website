{{--
    Native <select> (best accessibility and mobile behaviour) with a
    direction-aware chevron. `options` is a value => label map; a
    `placeholder` adds an empty first option.
--}}
@props([
    'name',
    'options' => [],
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'id' => null,
    'required' => false,
    'labelHidden' => false,
    'wrapperClass' => null,
])

@php
    $id ??= \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $current = \App\View\FormField::old($name, $value);
    $current = $current instanceof \BackedEnum ? $current->value : $current;
@endphp

<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :label-hidden="$labelHidden" @class([$wrapperClass])>
    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error)) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class(['form-control appearance-none pe-10']) }}
        >
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <x-ui.icon name="chevron-down" :size="18" class="pointer-events-none absolute inset-e-3 top-1/2 -translate-y-1/2 text-muted" />
    </div>
</x-ui.field>
