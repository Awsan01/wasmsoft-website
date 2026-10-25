{{--
    404 for content that exists, but not in the requested language. Lists
    the languages it can be read in (names in their own language and
    script) instead of silently showing another language.
--}}
@php
    $locales = app(\App\Localization\Locales::class);
@endphp

<x-layouts.site :seo="$seo" :language-urls="$alternates">
    <section class="container-page py-section">
        <div class="grid max-w-2xl gap-5">
            <span class="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
                <x-ui.icon name="languages" :size="24" />
            </span>

            <h1 class="text-h1">{{ __('site.missing.title') }}</h1>
            <p class="text-lead text-muted">{{ __('site.missing.text') }}</p>

            <div class="grid gap-3">
                <h2 class="text-h3">{{ __('site.missing.available_in') }}</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach ($alternates as $code => $url)
                        <li>
                            <x-ui.button
                                :href="$url"
                                variant="outline"
                                icon-end="arrow-right"
                                hreflang="{{ $code }}"
                                lang="{{ $code }}"
                                dir="{{ $locales->direction($code) }}"
                            >{{ $locales->nativeName($code) }}</x-ui.button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <x-ui.button :href="route('site.home')" variant="ghost" icon="home">{{ __('site.missing.home') }}</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.site>
