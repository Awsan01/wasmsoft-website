{{--
    Language selector. Each option is a real link (works without JS and is
    crawlable); names are shown in their own language and script, with the
    matching lang/dir attributes so they render and are pronounced correctly.
--}}
@props(['languages' => []])

@php
    $current = collect($languages)->firstWhere('current', true);
@endphp

@if (count($languages) > 1 && $current)
    <div {{ $attributes }}>
        <x-ui.dropdown id="language-menu" width="w-48" trigger-class="h-control-sm px-3 text-sm font-semibold text-ink hover:bg-lavender hover:text-primary">
            <x-slot:trigger>
                <x-ui.icon name="globe" :size="18" />
                <span class="sr-only">{{ __('ui.change_language') }}:</span>
                <span lang="{{ $current['code'] }}" dir="{{ $current['dir'] }}">{{ $current['name'] }}</span>
                <x-ui.icon name="chevron-down" :size="16" class="text-muted" />
            </x-slot:trigger>

            <ul class="grid gap-0.5">
                @foreach ($languages as $language)
                    <li>
                        <x-ui.dropdown-item
                            :href="$language['url']"
                            :active="$language['current']"
                            hreflang="{{ $language['code'] }}"
                            lang="{{ $language['code'] }}"
                            dir="{{ $language['dir'] }}"
                        >{{ $language['name'] }}</x-ui.dropdown-item>
                    </li>
                @endforeach
            </ul>
        </x-ui.dropdown>
    </div>
@endif
