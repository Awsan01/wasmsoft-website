<x-layouts.admin :title="__('admin.nav.overview')" :breadcrumbs="[['label' => __('admin.nav.overview'), 'url' => null]]">
    <x-admin.page-header
        :title="__('admin.overview.welcome', ['name' => $user->name])"
        :description="__('admin.overview.intro')"
    />

    <x-ui.card class="grid gap-3">
        <h2 class="text-h3">{{ __('admin.overview.your_role') }}</h2>
        <div class="flex flex-wrap items-center gap-3">
            <x-ui.badge tone="primary">{{ $user->role->label() }}</x-ui.badge>
            <p class="text-sm text-muted">{{ $user->role->description() }}</p>
        </div>
    </x-ui.card>
</x-layouts.admin>
