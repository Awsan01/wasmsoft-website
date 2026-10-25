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
    $formatted = $value instanceof \Carbon\CarbonInterface
        ? $value->copy()->setTimezone(config('wasmsoft.display_timezone'))->format('Y-m-d\TH:i')
        : $value;
@endphp

<x-ui.input type="datetime-local" :name="$name" :label="$label" :value="$formatted" :hint="$hint" dir="ltr" {{ $attributes }} />
