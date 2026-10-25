{{--
    Search and filter bar for dashboard lists (GET form, so filters stay in
    the URL and survive pagination). Extra filter fields go in the slot.
--}}
@props([
    'action',
    'search' => '',
    'active' => false,
])

<form method="GET" action="{{ $action }}" role="search" {{ $attributes->class(['flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end']) }}>
    <x-ui.input type="search" name="q" :value="$search" :label="__('admin.actions.search')" maxlength="100" wrapper-class="sm:w-72" />

    {{ $slot }}

    <div class="flex gap-2">
        <x-ui.button type="submit" variant="outline" icon="filter">{{ __('admin.actions.filter') }}</x-ui.button>
        @if ($active)
            <x-ui.button :href="$action" variant="ghost">{{ __('admin.actions.reset') }}</x-ui.button>
        @endif
    </div>
</form>
