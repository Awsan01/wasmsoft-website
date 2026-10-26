{{-- Plain-text part: printed as typed (plain text is never interpreted as HTML). --}}
{!! __('admin.mail.inquiry.heading') !!} · {!! $site !!}

@foreach ($details as $label => $value)
{!! $label !!}: {!! $value !!}
@endforeach

{!! $inquiry->message !!}

{!! __('admin.mail.inquiry.action') !!}: {!! $url !!}

{!! __('admin.mail.inquiry.reply_hint') !!}
