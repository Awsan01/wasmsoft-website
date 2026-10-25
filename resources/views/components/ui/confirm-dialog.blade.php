{{--
    The shared confirmation dialog used by resources/js/modules/confirm.js
    for forms marked with data-confirm="…". Rendered once per layout.
    Focus starts on "Cancel", the safe choice.
--}}
<dialog
    id="confirm-dialog"
    aria-labelledby="confirm-dialog-title"
    aria-describedby="confirm-dialog-message"
    class="m-auto w-[calc(100vw-2rem)] max-w-md overflow-hidden rounded-xl border border-border bg-surface p-0 text-ink shadow-lg"
>
    <div class="flex items-start gap-4 px-6 pt-6 pb-5">
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-error-soft text-error">
            <x-ui.icon name="alert-triangle" />
        </span>
        <div class="grid gap-1.5">
            <h2 id="confirm-dialog-title" class="text-h3" data-confirm-title>{{ __('ui.are_you_sure') }}</h2>
            <p id="confirm-dialog-message" class="text-sm text-muted" data-confirm-message></p>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3 border-t border-border bg-canvas px-6 py-4">
        <x-ui.button variant="outline" data-dialog-close autofocus>{{ __('ui.cancel') }}</x-ui.button>
        <x-ui.button variant="danger" data-confirm-accept>{{ __('ui.confirm') }}</x-ui.button>
    </div>
</dialog>
