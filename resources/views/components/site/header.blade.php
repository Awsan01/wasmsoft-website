<header
    data-site-header
    class="sticky top-0 z-(--z-header) border-b border-transparent bg-surface/90 backdrop-blur-md transition-base data-scrolled:border-border data-scrolled:shadow-sm"
>
    <div class="container-page flex h-header items-center gap-4 lg:gap-8">
        <x-ui.logo :href="route('site.home')" :name="$siteName" :src="$logoUrl" />

        <nav aria-label="{{ __('site.nav.primary') }}" class="hidden lg:block">
            <ul class="flex items-center gap-1">
                @foreach ($links as $link)
                    <li>
                        <a
                            href="{{ $link->url }}"
                            @if ($link->active) aria-current="page" @endif
                            @if ($link->newTab) target="_blank" rel="noopener noreferrer" @endif
                            class="block rounded-md px-3.5 py-2 text-sm font-semibold text-ink/80 transition-base hover:bg-lavender hover:text-primary aria-[current=page]:bg-lavender aria-[current=page]:text-primary"
                        >{{ $link->label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ms-auto flex items-center gap-2">
            <x-site.language-switcher :languages="$languages" class="hidden sm:block" />

            {{-- Visibility lives on a wrapper: utilities on the button itself would clash with its own display class. --}}
            <div class="hidden sm:block">
                <x-ui.button :href="route('site.contact')" size="sm" icon-end="arrow-right">
                    {{ __('site.nav.cta') }}
                </x-ui.button>
            </div>

            <button
                type="button"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="site-mobile-nav"
                aria-label="{{ __('ui.open_menu') }}"
                data-label-open="{{ __('ui.open_menu') }}"
                data-label-close="{{ __('ui.close_menu') }}"
                class="group inline-grid size-control-md place-items-center rounded-md text-ink transition-base hover:bg-lavender hover:text-primary lg:hidden"
            >
                <x-ui.icon name="menu" :size="22" class="group-aria-expanded:hidden" />
                <x-ui.icon name="x" :size="22" class="hidden group-aria-expanded:block" />
            </button>
        </div>
    </div>

    {{-- Mobile navigation --}}
    <div id="site-mobile-nav" hidden class="max-h-[calc(100dvh-var(--spacing-header))] overflow-y-auto border-t border-border bg-surface lg:hidden">
        <div class="container-page grid gap-6 py-6">
            <nav aria-label="{{ __('site.nav.primary') }}">
                <ul class="grid gap-1">
                    @foreach ($links as $link)
                        <li>
                            <a
                                href="{{ $link->url }}"
                                @if ($link->active) aria-current="page" @endif
                                @if ($link->newTab) target="_blank" rel="noopener noreferrer" @endif
                                class="flex items-center justify-between rounded-md px-3 py-3 text-base font-semibold text-ink transition-base hover:bg-lavender hover:text-primary aria-[current=page]:bg-lavender aria-[current=page]:text-primary"
                            >
                                {{ $link->label }}
                                <x-ui.icon name="chevron-right" :size="18" class="text-muted" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @if (count($languages) > 1)
                <div class="grid gap-2 border-t border-border pt-6">
                    <p class="text-eyebrow text-muted">{{ __('ui.language') }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($languages as $language)
                            <li>
                                <a
                                    href="{{ $language['url'] }}"
                                    hreflang="{{ $language['code'] }}"
                                    lang="{{ $language['code'] }}"
                                    dir="{{ $language['dir'] }}"
                                    @if ($language['current']) aria-current="true" @endif
                                    class="inline-flex h-control-sm items-center rounded-full border border-border px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=true]:border-primary aria-[current=true]:bg-lavender aria-[current=true]:text-primary"
                                >{{ $language['name'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.button :href="route('site.contact')" icon-end="arrow-right" block>
                {{ __('site.nav.cta') }}
            </x-ui.button>
        </div>
    </div>
</header>
