{{--
    The publishing panel of a content form: status (limited to `statuses`
    when the user may not publish), optional publication date, and the
    submit button. Extra actions (preview, view on site) go in the slot.
    `locked` shows the status read-only (system pages are always published).
--}}
@props([
    'content',
    'statuses' => null,
    'withDate' => false,
    'locked' => false,
])

<x-admin.section :title="__('admin.content.publishing')" {{ $attributes }}>
    @if ($locked)
        <div class="grid gap-2">
            <x-admin.status-badge :content="$content" class="justify-self-start" />
            <p class="text-sm text-muted">{{ __('admin.content.always_published') }}</p>
        </div>
    @else
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :options="$statuses ?? \App\Content\PublicationStatus::options()"
            :value="$content->status"
            required
        />

        @if ($withDate)
            <x-admin.datetime-input
                name="published_at"
                :label="__('admin.fields.published_at')"
                :value="$content->published_at"
                :hint="__('admin.content.published_at_hint', ['timezone' => config('wasmsoft.display_timezone')])"
            />
        @endif
    @endif

    <x-ui.button type="submit" icon="check" block>
        {{ $content->exists ? __('admin.actions.save') : __('admin.actions.create') }}
    </x-ui.button>

    @if ($slot->isNotEmpty())
        <div class="grid gap-2 border-t border-border pt-4">{{ $slot }}</div>
    @endif
</x-admin.section>
