<footer class="surface-dark relative overflow-hidden bg-purple-950 text-on-dark-muted">
    <div class="bg-diagonal pointer-events-none absolute inset-0 opacity-60" aria-hidden="true"></div>

    <div class="container-page relative grid gap-12 py-16 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:gap-16">
        <div class="grid content-start gap-5">
            <x-ui.logo :href="route('site.home')" :name="$siteName" :src="$logoUrl" tone="dark" />

            @if ($description)
                <p class="max-w-sm text-sm">{{ $description }}</p>
            @endif

            @if (count($socialLinks) > 0)
                <ul class="flex flex-wrap gap-1" aria-label="{{ __('site.footer.social') }}">
                    @foreach ($socialLinks as $social)
                        <li>
                            <x-ui.icon-button
                                :href="$social['url']"
                                :icon="$social['network']->icon()"
                                :label="$social['network']->label().' '.__('ui.opens_in_new_tab')"
                                variant="on-dark"
                                target="_blank"
                                rel="noopener noreferrer"
                            />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="grid gap-10 sm:grid-cols-3">
            @foreach ($groups as $group)
                <nav aria-labelledby="footer-group-{{ $loop->index }}">
                    <h2 id="footer-group-{{ $loop->index }}" class="text-sm font-semibold text-white">{{ $group->label }}</h2>
                    <ul class="mt-4 grid gap-3 text-sm">
                        @foreach ($group->children as $link)
                            <li>
                                <a
                                    href="{{ $link->url }}"
                                    @if ($link->newTab) target="_blank" rel="noopener noreferrer" @endif
                                    class="rounded-xs transition-base hover:text-white hover:underline"
                                >{{ $link->label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            @if ($email || $phone || $address)
                <div>
                    <h2 class="text-sm font-semibold text-white">{{ __('site.footer.contact') }}</h2>
                    <ul class="mt-4 grid gap-3 text-sm">
                        @if ($email)
                            <li class="flex items-start gap-2.5">
                                <x-ui.icon name="mail" :size="18" class="mt-0.5" />
                                <a href="mailto:{{ $email }}" class="break-all transition-base hover:text-white hover:underline" dir="ltr">{{ $email }}</a>
                            </li>
                        @endif
                        @if ($phone)
                            <li class="flex items-start gap-2.5">
                                <x-ui.icon name="phone" :size="18" class="mt-0.5" />
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="transition-base hover:text-white hover:underline" dir="ltr">{{ $phone }}</a>
                            </li>
                        @endif
                        @if ($address)
                            <li class="flex items-start gap-2.5">
                                <x-ui.icon name="map-pin" :size="18" class="mt-0.5" />
                                <span class="whitespace-pre-line">{{ $address }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="relative border-t border-white/10">
        <div class="container-page flex flex-col gap-2 py-6 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $siteName }}. {{ __('site.footer.rights') }}</p>
            @if ($note)
                <p>{{ $note }}</p>
            @endif
        </div>
    </div>
</footer>
