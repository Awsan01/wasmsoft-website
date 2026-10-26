<?php

namespace App\Notifications;

use App\Localization\Locales;
use App\Models\Inquiry;
use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the inbox configured in the settings about a new inquiry; replies
 * go straight to the visitor (Reply-To). The visitor's text is rendered by
 * dedicated, fully escaped views — not the Markdown mail template, where
 * typed Markdown would turn into links.
 *
 * No confirmation is ever sent to the visitor's address: the form must not
 * become a way to send e-mail to arbitrary people.
 */
class InquiryReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /** An inquiry deleted before the mail goes out is not sent. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public readonly Inquiry $inquiry) {}

    /**
     * @return list<string>
     */
    public function via(AnonymousNotifiable $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(AnonymousNotifiable $notifiable): MailMessage
    {
        $inquiry = $this->inquiry->loadMissing('service.translations');
        $site = app(Brand::class)->name();

        $details = array_filter([
            __('admin.inquiries.fields.name') => $inquiry->name,
            __('admin.inquiries.fields.email') => $inquiry->email,
            __('admin.inquiries.fields.phone') => $inquiry->phone,
            __('admin.inquiries.fields.company') => $inquiry->company,
            __('admin.inquiries.fields.service') => $inquiry->service?->displayTranslation()?->title,
            __('admin.inquiries.fields.locale') => app(Locales::class)->nativeName($inquiry->locale),
        ], filled(...));

        return (new MailMessage)
            ->subject(__('admin.mail.inquiry.subject', ['name' => $inquiry->name, 'site' => $site]))
            ->replyTo($inquiry->email, $inquiry->name)
            ->view(['mail.inquiry-received', 'mail.inquiry-received-text'], [
                'inquiry' => $inquiry,
                'details' => $details,
                'site' => $site,
                'url' => route('admin.inquiries.show', $inquiry),
            ]);
    }
}
