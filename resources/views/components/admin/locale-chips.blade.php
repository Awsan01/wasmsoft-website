{{--
    Which language versions a content item has: one chip per supported
    language, struck through when missing (never colour alone), with the
    full status for screen readers.
--}}
@props(['content'])

@php
    $locales = app(\App\Localization\Locales::class);
    $translated = $content->translatedLocales();
@endphp

<ul {{ $attributes->class(['flex gap-1']) }}>
    @foreach ($locales->supported() as $code)
        @php
            $exists = in_array($code, $translated, true);
        @endphp
        <li>
            <span @class([
                'inline-flex h-6 min-w-8 items-center justify-center rounded-sm px-1.5 text-xs font-bold uppercase',
                'bg-success-soft text-success' => $exists,
                'bg-canvas text-muted line-through' => ! $exists,
            ])>
                <span aria-hidden="true">{{ $code }}</span>
                <span class="sr-only">{{ $locales->nativeName($code) }}: {{ $exists ? __('admin.translations.present') : __('admin.translations.missing') }}</span>
            </span>
        </li>
    @endforeach
</ul>
