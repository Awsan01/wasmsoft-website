{{-- Delete form with the shared confirmation dialog (the server re-checks permission). --}}
@props([
    'action',
    'name',
    'block' => false,
])

<form method="POST" action="{{ $action }}" data-confirm="{{ __('admin.content.confirm_delete', ['name' => $name]) }}" data-confirm-accept="{{ __('admin.actions.delete') }}" {{ $attributes }}>
    @csrf
    @method('DELETE')
    <x-ui.button type="submit" variant="danger" icon="trash" size="sm" :block="$block">{{ __('admin.actions.delete') }}</x-ui.button>
</form>
