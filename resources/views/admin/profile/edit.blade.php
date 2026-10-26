<x-layouts.admin :title="__('admin.profile.title')" :breadcrumbs="[['label' => __('admin.profile.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.profile.title')" />

    <div class="grid min-w-0 gap-6 lg:grid-cols-2">
        <x-admin.section :title="__('admin.profile.account')" :description="__('admin.profile.email_hint')">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="grid gap-5" novalidate>
                @csrf
                @method('PUT')

                <x-ui.input name="name" :label="__('admin.users.fields.name')" :value="$user->name" maxlength="255" autocomplete="name" required />

                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.users.fields.email') }}</dt>
                        <dd class="font-semibold break-all" dir="ltr">{{ $user->email }}</dd>
                    </div>
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.users.fields.role') }}</dt>
                        <dd class="font-semibold">{{ $user->role->label() }}</dd>
                    </div>
                </dl>

                <x-ui.select
                    name="locale"
                    :label="__('admin.users.fields.locale')"
                    :value="$user->locale"
                    :placeholder="__('admin.users.browser_language')"
                    :options="$languages"
                />

                <div>
                    <x-ui.button type="submit" icon="check">{{ __('admin.actions.save') }}</x-ui.button>
                </div>
            </form>
        </x-admin.section>

        <x-admin.section :title="__('admin.profile.password')" :description="__('admin.profile.password_intro')">
            <form method="POST" action="{{ route('admin.profile.password') }}" class="grid gap-5" novalidate>
                @csrf
                @method('PUT')

                <x-ui.input type="password" name="current_password" :label="__('admin.profile.current_password')" autocomplete="current-password" maxlength="255" required />
                <x-ui.input type="password" name="password" :label="__('admin.auth.new_password')" :hint="__('admin.auth.password_rules')" autocomplete="new-password" maxlength="255" required />
                <x-ui.input type="password" name="password_confirmation" :label="__('admin.auth.confirm_password')" autocomplete="new-password" maxlength="255" required />

                <div>
                    <x-ui.button type="submit" icon="key-round">{{ __('admin.profile.change_password') }}</x-ui.button>
                </div>
            </form>
        </x-admin.section>
    </div>
</x-layouts.admin>
