{{--
    One text per language for a localized setting (company name, address…),
    submitted as `{name}[{locale}]`. Every supported language is listed, so
    texts can be prepared before a language is enabled. `required` names the
    language that must be filled in.
--}}
@props([
    'name',
    'label',
    'values' => [],
    'type' => 'text',
    'maxlength' => null,
    'rows' => 2,
    'required' => null,
    'hint' => null,
])

@php
    $locales = app(\App\Localization\Locales::class);
    $id = \App\View\FormField::id($name);
@endphp

<fieldset {{ $attributes->class(['grid min-w-0 gap-3']) }} @if ($hint) aria-describedby="{{ $id }}-hint" @endif>
    <legend class="mb-1 text-sm font-semibold text-ink">{{ $label }}</legend>

    @if ($hint)
        <p id="{{ $id }}-hint" class="-mt-1 text-sm text-muted">{{ $hint }}</p>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        @foreach ($locales->supported() as $code)
            @if ($type === 'textarea')
                <x-ui.textarea
                    :name="$name.'['.$code.']'"
                    :label="$locales->nativeName($code)"
                    :value="$values[$code] ?? null"
                    :rows="$rows"
                    :maxlength="$maxlength"
                    :required="$required === $code"
                    lang="{{ $code }}"
                    dir="{{ $locales->direction($code) }}"
                />
            @else
                <x-ui.input
                    :name="$name.'['.$code.']'"
                    :label="$locales->nativeName($code)"
                    :value="$values[$code] ?? null"
                    :maxlength="$maxlength"
                    :required="$required === $code"
                    lang="{{ $code }}"
                    dir="{{ $locales->direction($code) }}"
                />
            @endif
        @endforeach
    </div>
</fieldset>
