@php
    $formatter = app(\App\Localization\Formatter::class);
    $locales = app(\App\Localization\Locales::class);
@endphp

<x-layouts.admin
    :title="$media->original_name"
    :breadcrumbs="[
        ['label' => __('admin.media.title'), 'url' => route('admin.media.index')],
        ['label' => $media->original_name, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$media->original_name" :description="__('admin.media.edit_title')">
        <x-slot:actions>
            <x-ui.button :href="$media->url()" variant="outline" icon="external-link" target="_blank" rel="noopener">
                {{ __('admin.media.open_file') }}
                <span class="sr-only">{{ __('ui.opens_in_new_tab') }}</span>
            </x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="grid content-start gap-6">
            <x-admin.section>
                <div class="grid place-items-center overflow-hidden rounded-lg bg-canvas p-4">
                    @if ($media->isImage())
                        <img src="{{ $media->urlForWidth(960) }}" alt="" width="{{ $media->width }}" height="{{ $media->height }}" class="max-h-112 w-auto rounded-md object-contain">
                    @else
                        <x-ui.icon name="file-text" :size="64" class="text-secondary" />
                    @endif
                </div>
            </x-admin.section>

            @if ($media->isImage())
                <x-admin.section :title="__('admin.fields.alt')" :description="__('admin.media.alt_hint')">
                    @can('update', $media)
                        <form method="POST" action="{{ route('admin.media.update', $media) }}" class="grid gap-5" novalidate>
                            @csrf
                            @method('PUT')
                            @foreach ($locales->supported() as $code)
                                <x-ui.input
                                    name="alt[{{ $code }}]"
                                    :label="__('admin.translations.field', ['field' => __('admin.fields.alt'), 'language' => $locales->nativeName($code)])"
                                    :value="$media->altText($code)"
                                    maxlength="250"
                                    lang="{{ $code }}"
                                    dir="{{ $locales->direction($code) }}"
                                />
                            @endforeach
                            <div>
                                <x-ui.button type="submit" icon="check">{{ __('admin.actions.save') }}</x-ui.button>
                            </div>
                        </form>
                    @else
                        <dl class="grid gap-3 text-sm">
                            @foreach ($locales->supported() as $code)
                                <div class="grid gap-0.5">
                                    <dt class="font-semibold">{{ $locales->nativeName($code) }}</dt>
                                    <dd lang="{{ $code }}" dir="{{ $locales->direction($code) }}" class="text-muted">{{ $media->altText($code) ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endcan
                </x-admin.section>
            @endif
        </div>

        <div class="grid content-start gap-6">
            <x-admin.section :title="__('admin.media.details')">
                <dl class="grid gap-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.type') }}</dt><dd class="font-medium">{{ $media->kind->label() }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.size') }}</dt><dd class="font-medium"><bdi>{{ $formatter->fileSize($media->size) }}</bdi></dd></div>
                    @if ($media->width)
                        <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.dimensions') }}</dt><dd class="font-medium"><bdi>{{ $media->width }}×{{ $media->height }}</bdi></dd></div>
                    @endif
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.uploaded_at') }}</dt><dd class="font-medium"><x-ui.time :value="$media->created_at" format="datetime" /></dd></div>
                    @if ($media->uploader)
                        <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.uploaded_by') }}</dt><dd class="font-medium">{{ $media->uploader->name }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('admin.media.usage') }}</dt><dd class="font-medium">{{ trans_choice('admin.media.usage_count', $usageCount, ['count' => $usageCount]) }}</dd></div>
                </dl>

                <div class="grid gap-1.5">
                    <label for="media-url" class="text-sm font-semibold">{{ __('admin.media.url') }}</label>
                    <div class="flex gap-2">
                        <input id="media-url" type="text" value="{{ $media->url() }}" readonly dir="ltr" class="form-control min-w-0 flex-1 text-sm">
                        <x-ui.button variant="outline" size="sm" icon="copy" data-copy="#media-url" data-copied-label="{{ __('admin.actions.copied') }}" class="mt-1">
                            {{ __('admin.actions.copy') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-admin.section>

            @can('delete', $media)
                <x-admin.section>
                    <form method="POST" action="{{ route('admin.media.destroy', $media) }}" data-confirm="{{ __('admin.content.confirm_delete', ['name' => $media->original_name]) }}" data-confirm-accept="{{ __('admin.actions.delete') }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" icon="trash" block>{{ __('admin.actions.delete') }}</x-ui.button>
                    </form>
                </x-admin.section>
            @endcan
        </div>
    </div>
</x-layouts.admin>
