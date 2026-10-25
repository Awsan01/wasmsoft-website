{{-- Publication status of a content item; "Scheduled" when published with a future date. --}}
@props(['content'])

@php
    $status = $content->status;
    $scheduled = $status === \App\Content\PublicationStatus::Published && $content->published_at?->isFuture();
@endphp

@if ($scheduled)
    <x-ui.badge tone="primary" dot {{ $attributes }}>{{ __('admin.content.scheduled') }}</x-ui.badge>
@else
    <x-ui.badge :tone="$status->tone()" dot {{ $attributes }}>{{ $status->label() }}</x-ui.badge>
@endif
