{{--
    "Move up" / "move down" for manually ordered lists. Plain forms, so
    reordering works by keyboard and without JavaScript. `label` names
    the item for screen readers ("Move up: Web development").
--}}
@props([
    'url',
    'label',
    'first' => false,
    'last' => false,
])

<div {{ $attributes->class(['flex gap-0.5']) }}>
    @foreach (['up' => [$first, 'arrow-up', 'admin.actions.move_up'], 'down' => [$last, 'arrow-down', 'admin.actions.move_down']] as $direction => [$disabled, $icon, $text])
        <form method="POST" action="{{ $url }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="direction" value="{{ $direction }}">
            <x-ui.icon-button
                type="submit"
                :icon="$icon"
                :label="__($text).': '.$label"
                size="sm"
                variant="outline"
                :disabled="$disabled"
            />
        </form>
    @endforeach
</div>
