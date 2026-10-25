{{-- Eyebrow + heading + supporting text, used to open page sections. --}}
@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'align' => 'start',
    'as' => 'h2',
    'tone' => 'default',
])

@php
    $centered = $align === 'center';
    $dark = $tone === 'dark';
@endphp

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $centered]) }}>
    @if ($eyebrow)
        <p @class([
            'flex items-center gap-2.5 text-eyebrow',
            'justify-center' => $centered,
            'text-secondary' => ! $dark,
            'text-on-dark-muted' => $dark,
        ])>
            <span class="slash-mark" aria-hidden="true"></span>
            {{ $eyebrow }}
        </p>
    @endif

    <{{ $as }} @class(['text-h2', 'mt-3' => $eyebrow, 'text-white' => $dark])>{{ $title }}</{{ $as }}>

    @if ($description)
        <p @class(['mt-4 text-lead', 'text-muted' => ! $dark, 'text-on-dark-muted' => $dark])>{{ $description }}</p>
    @endif
</div>
