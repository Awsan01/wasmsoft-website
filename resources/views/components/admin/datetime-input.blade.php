{{--
    Date and time field shown in the display timezone (config
    wasmsoft.display_timezone); App\Support\DateInput converts the posted
    value back to UTC.
--}}
@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
])

@php
    $formatted = $value instanceof \Carbon\CarbonInterface ? \App\Support\DateInput::format($value) : $value;
@endphp

<x-ui.input type="datetime-local" :name="$name" :label="$label" :value="$formatted" :hint="$hint" dir="ltr" {{ $attributes }} />
