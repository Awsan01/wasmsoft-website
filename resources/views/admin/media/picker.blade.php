{{-- Contents of the media picker dialog, loaded and refreshed by resources/js/modules/media-picker.js. --}}
<div class="grid gap-4" data-media-results>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form data-media-search role="search" class="flex gap-2">
            <label for="media-picker-search" class="sr-only">{{ __('admin.media.picker.search') }}</label>
            <input id="media-picker-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="{{ __('admin.media.picker.search') }}" class="form-control sm:w-64">
            <x-ui.button type="submit" variant="outline" icon="search">{{ __('admin.actions.search') }}</x-ui.button>
        </form>

        @if ($canUpload)
            <div class="grid gap-1">
                <label class="inline-flex h-control-md cursor-pointer items-center gap-2 rounded-md bg-lavender px-5 text-sm font-semibold text-primary transition-base hover:bg-lavender-strong focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-secondary">
                    <x-ui.icon name="upload" :size="18" />
                    {{ __('admin.media.picker.upload') }}
                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="sr-only"
                        data-media-upload
                        data-upload-url="{{ route('admin.media.store') }}"
                        data-uploading-label="{{ __('ui.loading') }}"
                        data-error-label="{{ __('admin.media.errors.type') }}"
                    >
                </label>
                <p class="text-sm text-muted" role="status" data-media-upload-status></p>
            </div>
        @endif
    </div>

    @if ($images->isEmpty())
        <p class="rounded-lg border border-dashed border-purple-300 bg-canvas p-6 text-center text-sm text-muted">{{ __('admin.media.picker.empty') }}</p>
    @else
        <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
            @foreach ($images as $image)
                <li>
                    <button
                        type="button"
                        data-media-option
                        data-media-id="{{ $image->id }}"
                        data-media-url="{{ $image->urlForWidth(480) }}"
                        data-media-name="{{ $image->original_name }}"
                        class="block w-full overflow-hidden rounded-md border-2 border-transparent bg-canvas transition-base hover:border-secondary focus-visible:border-secondary"
                    >
                        <img src="{{ $image->urlForWidth(480) }}" alt="" loading="lazy" decoding="async" class="aspect-square w-full object-cover">
                        <span class="sr-only">{{ __('admin.media.picker.select', ['name' => $image->original_name]) }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        {{ $images->links('pagination.simple') }}
    @endif
</div>
