<x-layouts.auth :title="__('admin.auth.reset_title')">
    <div class="grid gap-2">
        <h1 class="text-h2">{{ __('admin.auth.reset_title') }}</h1>
        <p class="text-sm text-muted">{{ __('admin.auth.reset_intro') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.password.store') }}" class="mt-6 grid gap-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.input
            name="email"
            type="email"
            :label="__('admin.auth.email')"
            :value="$email"
            autocomplete="username"
            maxlength="255"
            dir="ltr"
            required
        />

        <x-ui.input
            name="password"
            type="password"
            :label="__('admin.auth.new_password')"
            :hint="__('admin.auth.password_rules')"
            autocomplete="new-password"
            maxlength="255"
            required
            autofocus
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            :label="__('admin.auth.confirm_password')"
            autocomplete="new-password"
            maxlength="255"
            required
        />

        <x-ui.button type="submit" icon-end="check" block>{{ __('admin.auth.reset_submit') }}</x-ui.button>
    </form>
</x-layouts.auth>
