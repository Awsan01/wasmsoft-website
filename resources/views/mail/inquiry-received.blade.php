{{-- New inquiry for the team inbox (HTML part). Everything the visitor typed is escaped; nothing is read as Markdown. --}}
@php
    $mailLocale = app()->getLocale();
    $mailDirection = app(\App\Localization\Locales::class)->direction($mailLocale);
@endphp
<!DOCTYPE html>
<html lang="{{ $mailLocale }}" dir="{{ $mailDirection }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>{{ __('admin.mail.inquiry.heading') }}</title>
</head>
<body style="margin:0;padding:24px 12px;background-color:#f5f0fa;font-family:Arial,Helvetica,sans-serif;color:#21152b;" dir="{{ $mailDirection }}">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border:1px solid #e8e1ed;border-radius:12px;">
<tr>
<td style="padding:28px 32px;">
<p style="margin:0 0 4px;font-size:13px;font-weight:bold;color:#6d28a0;">{{ $site }}</p>
<h1 style="margin:0 0 20px;font-size:22px;line-height:1.3;color:#21152b;">{{ __('admin.mail.inquiry.heading') }}</h1>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.5;">
@foreach ($details as $label => $value)
<tr>
<td style="padding:6px 12px;vertical-align:top;white-space:nowrap;color:#6f6578;">{{ $label }}</td>
<td style="padding:6px 12px;vertical-align:top;" dir="auto">{{ $value }}</td>
</tr>
@endforeach
</table>

<div style="margin:20px 0;padding:16px;background-color:#faf7fd;border-radius:8px;font-size:14px;line-height:1.6;" dir="auto">{!! nl2br(e($inquiry->message)) !!}</div>

<p style="margin:0 0 16px;">
<a href="{{ $url }}" style="display:inline-block;padding:10px 18px;background-color:#481467;border-radius:8px;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;">{{ __('admin.mail.inquiry.action') }}</a>
</p>
<p style="margin:0;font-size:12px;line-height:1.5;color:#6f6578;">{{ __('admin.mail.inquiry.reply_hint') }}</p>
</td>
</tr>
</table>
</body>
</html>
