{{-- The shared image picker dialog (filled by resources/js/modules/media-picker.js). --}}
<x-ui.dialog id="media-picker-dialog" :title="__('admin.media.picker.title')" size="xl">
    <div data-media-picker-body aria-live="polite" class="min-h-48">
        <x-ui.spinner :label="__('ui.loading')" class="text-primary" />
    </div>
</x-ui.dialog>
