{{--
    For each language version of a content item: "View in …" when it is
    live, otherwise "Preview in …" (dashboard preview of the unpublished
    version). Opens in a new tab.
--}}
@props([
    'content',
    'previewRoute',
])

@php
    $locales = app(\App\Localization\Locales::class);
@endphp

@if ($content->exists)
    <ul class="grid gap-2">
        @foreach ($locales->supported() as $code)
            @if ($content->hasTranslation($code))
                @php
                    $liveUrl = $content->publicUrl($code);
                @endphp
                <li>
                    <a
                        href="{{ $liveUrl ?? route($previewRoute, [$content, $code]) }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xs text-sm font-semibold text-secondary hover:underline"
                    >
                        <x-ui.icon :name="$liveUrl ? 'external-link' : 'eye'" :size="16" />
                        {{ __($liveUrl ? 'admin.content.view_in' : 'admin.content.preview_in', ['language' => $locales->nativeName($code)]) }}
                        <span class="sr-only">{{ __('ui.opens_in_new_tab') }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif
