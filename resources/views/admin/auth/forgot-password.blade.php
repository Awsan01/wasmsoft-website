<x-layouts.auth :title="__('admin.auth.forgot_title')">
    <div class="grid gap-2">
        <h1 class="text-h2">{{ __('admin.auth.forgot_title') }}</h1>
        <p class="text-sm text-muted">{{ __('admin.auth.forgot_intro') }}</p>
    </div>

    @if (session('status'))
        <x-ui.alert tone="success" class="mt-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}" class="mt-6 grid gap-5" novalidate>
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

        <x-ui.button type="submit" icon-end="send" block>{{ __('admin.auth.send_link') }}</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 rounded-xs font-semibold text-secondary hover:underline">
            <x-ui.icon name="arrow-left" :size="16" />
            {{ __('admin.auth.back_to_sign_in') }}
        </a>
    </p>
</x-layouts.auth>
