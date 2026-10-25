{{--
    Choice of a brand icon (App\Content\ContentIcon) as a group of radio
    buttons: icons for sighted users, translated names for screen readers
    and as tooltips. Works with the keyboard (arrow keys within the group).
--}}
@props([
    'name',
    'label',
    'value' => null,
])

@php
    $id = \App\View\FormField::id($name);
    $error = \App\View\FormField::error($name);
    $current = \App\View\FormField::old($name, $value instanceof \BackedEnum ? $value->value : $value);
    $tile = 'grid aspect-square cursor-pointer place-items-center rounded-md border border-border bg-surface text-muted transition-base hover:border-purple-400 hover:text-primary has-checked:border-primary has-checked:bg-lavender has-checked:text-primary has-checked:ring-2 has-checked:ring-primary has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary';
@endphp

<fieldset {{ $attributes->class(['grid gap-2']) }} @if ($error) aria-describedby="{{ $id }}-error" @endif>
    <legend class="mb-2 text-sm font-semibold text-ink">{{ $label }}</legend>

    <div class="grid grid-cols-5 gap-2">
        <label class="{{ $tile }}" title="{{ __('admin.icons.none') }}">
            <input type="radio" name="{{ $name }}" value="" class="sr-only" @checked(blank($current))>
            <x-ui.icon name="ban" :size="20" />
            <span class="sr-only">{{ __('admin.icons.none') }}</span>
        </label>

        @foreach (\App\Content\ContentIcon::cases() as $icon)
            <label class="{{ $tile }}" title="{{ $icon->label() }}">
                <input type="radio" name="{{ $name }}" value="{{ $icon->value }}" class="sr-only" @checked($current === $icon->value)>
                <x-ui.icon :name="$icon->value" :size="20" />
                <span class="sr-only">{{ $icon->label() }}</span>
            </label>
        @endforeach
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-sm font-medium text-error">
            <x-ui.icon name="alert-circle" :size="16" class="mt-[0.2rem]" />
            <span>{{ $error }}</span>
        </p>
    @endif
</fieldset>
