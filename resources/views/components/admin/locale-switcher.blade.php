{{--
    Dashboard language selector (sign-in pages and top bar). Each option is
    a small POST form, so the choice is CSRF-protected and stored on the
    account (or in the session before signing in).
--}}
@php
    $locales = app(\App\Localization\Locales::class);
    $current = app()->getLocale();
@endphp

<x-ui.dropdown id="admin-language-menu" width="w-48" trigger-class="h-control-sm px-3 text-sm font-semibold text-ink hover:bg-lavender hover:text-primary">
    <x-slot:trigger>
        <x-ui.icon name="languages" :size="18" />
        <span class="sr-only">{{ __('admin.topbar.language') }}:</span>
        <span lang="{{ $current }}" dir="{{ $locales->direction($current) }}" class="hidden sm:inline">{{ $locales->nativeName($current) }}</span>
        <x-ui.icon name="chevron-down" :size="16" class="text-muted" />
    </x-slot:trigger>

    <ul class="grid gap-0.5">
        @foreach ($locales->supported() as $code)
            <li>
                <form method="POST" action="{{ route('admin.locale') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <x-ui.dropdown-item
                        type="submit"
                        :active="$code === $current"
                        lang="{{ $code }}"
                        dir="{{ $locales->direction($code) }}"
                        :aria-current="$code === $current ? 'true' : null"
                    >{{ $locales->nativeName($code) }}</x-ui.dropdown-item>
                </form>
            </li>
        @endforeach
    </ul>
</x-ui.dropdown>
