{{--
    Text-like input with label, hint and validation error.
    Extra attributes (autocomplete, maxlength, dir, lang, placeholder…) are
    forwarded to the <input>. Use `wrapperClass` to style the wrapper.
--}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'id' => null,
    'required' => false,
    'labelHidden' => false,
    'wrapperClass' => null,
])

@php
    $id ??= \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $current = $type === 'password' ? null : \App\View\FormField::old($name, $value);
@endphp

<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :label-hidden="$labelHidden" @class([$wrapperClass])>
    <input
        type="{{ $type }}"
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($current !== null) value="{{ $current }}" @endif
        @required($required)
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error)) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->class(['form-control']) }}
    >
</x-ui.field>
