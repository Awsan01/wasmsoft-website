{{--
    A machine-readable <time> with a localized label.
    `format`: date (default), datetime or relative ("3 days ago", with the
    full date as a tooltip).
--}}
@props([
    'value',
    'format' => 'date',
    'style' => null,
])

@php
    $formatter = app(\App\Localization\Formatter::class);
    $label = match ($format) {
        'datetime' => $formatter->dateTime($value, $style ?? 'medium'),
        'relative' => $formatter->relative($value),
        default => $formatter->date($value, $style ?? 'long'),
    };
@endphp

<time
    datetime="{{ $value->toAtomString() }}"
    @if ($format === 'relative') title="{{ $formatter->dateTime($value) }}" @endif
    {{ $attributes }}
>{{ $label }}</time>
