@php
    $formatter = app(\App\Localization\Formatter::class);
@endphp

<x-layouts.admin :title="__('admin.media.title')" :breadcrumbs="[['label' => __('admin.media.title'), 'url' => null]]">
    <x-admin.page-header :title="__('admin.media.title')" :description="__('admin.media.description')" />

    @can('create', \App\Models\Media::class)
        <x-admin.section :title="__('admin.media.upload_title')">
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start" novalidate>
                @csrf
                <x-ui.file-input
                    name="files[]"
                    :label="__('admin.media.files_label')"
                    :hint="__('admin.media.upload_hint', [
                        'image_mb' => intdiv(\App\Media\MediaKind::Image->maxKilobytes(), 1024),
                        'document_mb' => intdiv(\App\Media\MediaKind::Document->maxKilobytes(), 1024),
                        'count' => \App\Http\Requests\Admin\StoreMediaRequest::MAX_FILES,
                    ])"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                    multiple
                    required
                />
                <x-ui.button type="submit" icon="upload" class="sm:mt-7">{{ __('admin.actions.upload') }}</x-ui.button>
            </form>

            @if ($errors->has('files.*'))
                <ul class="grid gap-1 text-sm font-medium text-error">
                    @foreach ($errors->get('files.*') as $messages)
                        @foreach ($messages as $message)
                            <li class="flex items-start gap-1.5"><x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" /> {{ $message }}</li>
                        @endforeach
                    @endforeach
                </ul>
            @endif
        </x-admin.section>
    @endcan

    <form method="GET" action="{{ route('admin.media.index') }}" role="search" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <x-ui.input type="search" name="q" :value="$search" :label="__('admin.actions.search')" wrapper-class="sm:w-80" maxlength="100" />
        <x-ui.select
            name="kind"
            :label="__('admin.media.type')"
            :value="$kind?->value"
            :placeholder="__('admin.media.all_kinds')"
            :options="collect(\App\Media\MediaKind::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all()"
            wrapper-class="sm:w-56"
        />
        <div class="flex gap-2">
            <x-ui.button type="submit" variant="outline" icon="filter">{{ __('admin.actions.filter') }}</x-ui.button>
            @if ($search !== '' || $kind !== null)
                <x-ui.button :href="route('admin.media.index')" variant="ghost">{{ __('admin.actions.reset') }}</x-ui.button>
            @endif
        </div>
    </form>

    @if ($media->isEmpty())
        <x-ui.empty-state :title="$search !== '' || $kind !== null ? __('admin.content.no_results') : __('admin.media.empty_title')" icon="image">
            @if ($search === '' && $kind === null)
                {{ __('admin.media.empty_text') }}
            @endif
        </x-ui.empty-state>
    @else
        <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            @foreach ($media as $item)
                <li>
                    <a href="{{ route('admin.media.edit', $item) }}" class="group grid h-full overflow-hidden rounded-lg border border-border bg-surface shadow-xs transition-base hover:border-purple-300 hover:shadow-md">
                        <span class="grid aspect-4/3 place-items-center overflow-hidden bg-canvas">
                            @if ($item->isImage())
                                <img src="{{ $item->urlForWidth(480) }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition-base group-hover:scale-105">
                            @else
                                <x-ui.icon name="file-text" :size="36" class="text-secondary" />
                            @endif
                        </span>
                        <span class="grid gap-0.5 p-3">
                            <span class="truncate text-sm font-semibold text-ink">{{ $item->original_name }}</span>
                            <span class="text-xs text-muted">
                                <bdi>{{ $formatter->fileSize($item->size) }}</bdi>
                                @if ($item->width)
                                    · <bdi>{{ $item->width }}×{{ $item->height }}</bdi>
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        {{ $media->links() }}
    @endif
</x-layouts.admin>
