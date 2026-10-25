<header class="sticky top-0 z-(--z-header) border-b border-border bg-surface/95 backdrop-blur-md">
    <div class="flex h-header items-center gap-3 px-gutter">
        <div class="flex items-center gap-2 lg:hidden">
            <x-ui.icon-button icon="menu" :label="__('admin.nav.open')" data-dialog-open="admin-drawer" class="-ms-2" />
            <x-ui.logo :href="route('admin.dashboard')" :name="$siteName" :src="$logoUrl" :size="30" :show-name="false" />
        </div>

        <x-ui.breadcrumbs :items="$breadcrumbs" class="hidden min-w-0 md:block" />

        <div class="ms-auto flex items-center gap-1 sm:gap-2">
            {{-- Visibility lives on a wrapper: utilities on the button itself would clash with its own display class. --}}
            <div class="hidden sm:block">
                <x-ui.button :href="$siteUrl" variant="ghost" size="sm" icon="external-link" target="_blank" rel="noopener">
                    {{ __('admin.topbar.view_site') }}
                    <span class="sr-only">{{ __('ui.opens_in_new_tab') }}</span>
                </x-ui.button>
            </div>

            <x-admin.locale-switcher />

            <x-ui.dropdown id="account-menu" width="w-64" trigger-class="h-control-md ps-1.5 pe-2.5 hover:bg-lavender">
                <x-slot:trigger>
                    <span class="grid size-8 place-items-center rounded-full bg-primary text-sm font-bold text-white" aria-hidden="true">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                    </span>
                    <span class="sr-only">{{ __('admin.topbar.account') }}</span>
                    <span class="hidden max-w-40 truncate text-sm font-semibold text-ink md:block">{{ $user->name }}</span>
                    <x-ui.icon name="chevron-down" :size="16" class="text-muted" />
                </x-slot:trigger>

                <div class="grid gap-1 border-b border-border px-3 pt-2 pb-3">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="truncate text-xs text-muted" dir="ltr">{{ $user->email }}</p>
                    <x-ui.badge tone="primary" class="mt-1 justify-self-start">{{ $user->role->label() }}</x-ui.badge>
                </div>

                <ul class="grid gap-0.5 pt-1.5">
                    @if (Route::has('admin.profile.edit'))
                        <li>
                            <x-ui.dropdown-item :href="route('admin.profile.edit')" icon="user" :active="request()->routeIs('admin.profile.*')">
                                {{ __('admin.topbar.profile') }}
                            </x-ui.dropdown-item>
                        </li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <x-ui.dropdown-item type="submit" icon="log-out">{{ __('admin.topbar.sign_out') }}</x-ui.dropdown-item>
                        </form>
                    </li>
                </ul>
            </x-ui.dropdown>
        </div>
    </div>
</header>
