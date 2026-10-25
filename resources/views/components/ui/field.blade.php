{{--
    Label + control + hint + error wrapper shared by every form control.
    Controls compute their id / aria-describedby with App\View\FormField and
    pass them here so the label, hint and error are programmatically linked.
--}}
@props([
    'id',
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'labelHidden' => false,
])

<div {{ $attributes->class(['grid content-start gap-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" @class(['text-sm font-semibold text-ink', 'sr-only' => $labelHidden])>
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
