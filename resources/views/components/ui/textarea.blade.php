@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'id' => null,
    'rows' => 5,
    'required' => false,
    'labelHidden' => false,
    'wrapperClass' => null,
])

@php
    $id ??= \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $current = \App\View\FormField::old($name, $value);
    // List values (e.g. benefits) are edited one item per line.
    $current = is_array($current) ? implode("\n", $current) : $current;
@endphp

<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :label-hidden="$labelHidden" @class([$wrapperClass])>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy = \App\View\FormField::describedBy($id, (bool) $hint, (bool) $error)) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->class(['form-control']) }}
    >{{ $current }}</textarea>
</x-ui.field>
