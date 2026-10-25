<x-layouts.auth :title="__('admin.auth.sign_in')">
    <div class="grid gap-2">
        <h1 class="text-h2">{{ __('admin.auth.sign_in_title') }}</h1>
        <p class="text-sm text-muted">{{ __('admin.auth.sign_in_intro') }}</p>
    </div>

    @if (session('status'))
        <x-ui.alert tone="success" class="mt-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 grid gap-5" novalidate>
        @csrf

        <x-ui.input
            name="email"
            type="email"
            :label="__('admin.auth.email')"
            autocomplete="username"
            maxlength="255"
            dir="ltr"
            required
            autofocus
        />

        <x-ui.input
            name="password"
            type="password"
            :label="__('admin.auth.password')"
            autocomplete="current-password"
            maxlength="255"
            required
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-ui.checkbox name="remember" :label="__('admin.auth.remember')" :with-hidden="false" />
            <a href="{{ route('admin.password.request') }}" class="rounded-xs text-sm font-semibold text-secondary hover:underline">
                {{ __('admin.auth.forgot') }}
            </a>
        </div>

        <x-ui.button type="submit" icon-end="log-in" block>{{ __('admin.auth.sign_in') }}</x-ui.button>
    </form>
</x-layouts.auth>
