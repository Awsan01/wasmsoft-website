{{--
    A headline number on the overview: label, value and an optional detail
    line. With `href`, the whole tile links to the matching list.
--}}
@props([
    'label',
    'value',
    'detail' => null,
    'href' => null,
    'icon' => null,
])

<div {{ $attributes->class([
    'relative grid content-start gap-2 rounded-xl border border-border bg-surface p-5 shadow-xs',
    'transition-base hover:border-purple-300 hover:shadow-md has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary' => $href,
]) }}>
    <span class="flex items-center gap-2 text-sm font-medium text-muted">
        @if ($icon)
            <x-ui.icon :name="$icon" :size="18" class="text-secondary" />
        @endif
        @if ($href)
            <a href="{{ $href }}" class="after:absolute after:inset-0 after:rounded-xl focus:outline-none">{{ $label }}</a>
        @else
            {{ $label }}
        @endif
    </span>
    <span class="text-3xl leading-none font-semibold text-ink">{{ $value }}</span>
    @if ($detail)
        <span class="text-sm text-muted">{{ $detail }}</span>
    @endif
</div>
