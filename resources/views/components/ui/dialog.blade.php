{{--
    Modal dialog built on the native <dialog> element: the browser provides
    focus trapping, Escape to close and the inert backdrop. Open it with any
    element carrying data-dialog-open="{id}"; elements with
    data-dialog-close inside it close it.
--}}
@props([
    'id',
    'title',
    'description' => null,
    'size' => 'md',
])

@php
    $widths = ['sm' => 'max-w-md', 'md' => 'max-w-lg', 'lg' => 'max-w-3xl', 'xl' => 'max-w-5xl'];
@endphp

<dialog
    id="{{ $id }}"
    aria-labelledby="{{ $id }}-title"
    @if ($description) aria-describedby="{{ $id }}-description" @endif
    {{ $attributes->class([
        'm-auto w-[calc(100vw-2rem)] max-h-[calc(100dvh-2rem)] overflow-hidden rounded-xl border border-border bg-surface p-0 text-ink shadow-lg',
        $widths[$size] ?? $widths['md'],
    ]) }}
>
    <div class="flex max-h-[calc(100dvh-2rem)] flex-col">
        <header class="flex items-start justify-between gap-4 border-b border-border px-6 py-4">
            <div class="grid gap-1">
                <h2 id="{{ $id }}-title" class="text-h3">{{ $title }}</h2>
                @if ($description)
                    <p id="{{ $id }}-description" class="text-sm text-muted">{{ $description }}</p>
                @endif
            </div>
            <x-ui.icon-button icon="x" :label="__('ui.close')" size="sm" data-dialog-close class="-me-2" />
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>

        @isset($footer)
            <footer class="flex flex-wrap items-center justify-end gap-3 border-t border-border bg-canvas px-6 py-4">
                {{ $footer }}
            </footer>
        @endisset
    </div>
</dialog>
