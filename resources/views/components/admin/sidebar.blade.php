<div class="flex h-full flex-col">
    <div class="flex h-header shrink-0 items-center justify-between gap-3 border-b border-border px-5">
        <x-ui.logo :href="route('admin.dashboard')" :name="$siteName" :src="$logoUrl" :size="34" />

        @if ($drawer)
            <x-ui.icon-button icon="x" :label="__('admin.nav.close')" size="sm" data-dialog-close />
        @endif
    </div>

    <nav aria-label="{{ __('admin.nav.label') }}" class="flex-1 overflow-y-auto px-3 py-5">
        <div class="grid gap-6">
            @foreach ($groups as $group)
                <div class="grid gap-1.5">
                    @if ($group['label'])
                        <p id="{{ $idPrefix }}-group-{{ $group['key'] }}" class="px-3 text-overline text-muted">{{ $group['label'] }}</p>
                    @endif

                    <ul class="grid gap-0.5" @if ($group['label']) aria-labelledby="{{ $idPrefix }}-group-{{ $group['key'] }}" @endif>
                        @foreach ($group['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link->url }}"
                                    @if ($link->active) aria-current="page" @endif
                                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-ink/80 transition-base hover:bg-lavender hover:text-primary aria-[current=page]:bg-lavender aria-[current=page]:font-semibold aria-[current=page]:text-primary"
                                >
                                    <x-ui.icon :name="$link->icon" :size="18" />
                                    <span class="min-w-0 flex-1 truncate">{{ $link->label }}</span>
                                    @if ($link->badge)
                                        <x-ui.badge tone="primary">
                                            {{ app(\App\Localization\Formatter::class)->number($link->badge) }}
                                            <span class="sr-only">{{ __('admin.nav.new_items') }}</span>
                                        </x-ui.badge>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>
</div>
