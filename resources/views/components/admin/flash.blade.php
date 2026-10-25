{{-- One-time messages set by redirects: `status` (success), `warning` and `error`. --}}
@if (session('status'))
    <x-ui.alert tone="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

@if (session('warning'))
    <x-ui.alert tone="warning" dismissible>{{ session('warning') }}</x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert tone="error" dismissible>{{ session('error') }}</x-ui.alert>
@endif

@if ($errors->any())
    <x-ui.alert tone="error" :title="__('admin.form.errors_title')">
        {{ trans_choice('admin.form.errors_count', $errors->count(), ['count' => $errors->count()]) }}
    </x-ui.alert>
@endif
