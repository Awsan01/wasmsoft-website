<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Inquiries\InquiryStatus;
use App\Localization\Locales;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Service;
use App\Notifications\InquiryReceived;
use App\Security\SpamGuard;
use App\Seo\SeoMeta;
use App\Settings\ContactDetails;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * The contact page and its form (session-backed routes: CSRF, old input).
 */
class ContactController extends Controller
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SpamGuard $guard,
    ) {}

    public function show(): View
    {
        $locale = app()->getLocale();
        $intro = Page::query()->system(SystemPage::Contact)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.contact.title');

        $services = Service::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->ordered()
            ->get()
            ->mapWithKeys(fn (Service $service): array => [$service->id => (string) $service->translation($locale)?->title])
            ->all();

        return view('site.contact', [
            'title' => $title,
            'lead' => $intro?->summary ?: __('site.contact.lead'),
            'contact' => ContactDetails::from($this->settings, $locale),
            'services' => $services,
            'token' => $this->guard->tokenFor(old(SpamGuard::TOKEN)),
            'seo' => new SeoMeta(
                title: ($intro?->meta_title ?: $title).' · '.$this->brand->name(),
                description: $intro?->meta_description ?: Markdown::toText($intro?->summary) ?: __('site.contact.lead'),
                canonical: route('site.contact'),
                alternates: Alternates::route('site.contact', $this->locales),
            ),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $suspicious = $this->guard->isSuspicious($request);

        $inquiry = new Inquiry([
            ...$request->safe()->only(['name', 'email', 'phone', 'company', 'message']),
            'service_id' => $request->filled('service_id') ? $request->integer('service_id') : null,
            'locale' => app()->getLocale(),
            'consented_at' => now(),
        ]);
        $inquiry->status = $suspicious ? InquiryStatus::Spam : InquiryStatus::New;
        $inquiry->save();

        $recipient = $this->settings->get(SettingKey::InquiryNotificationEmail);

        if (! $suspicious && is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false) {
            Notification::route('mail', $recipient)->notify((new InquiryReceived($inquiry))->locale($this->locales->default()));
        }

        if ($suspicious) {
            // No personal data in the log: the stored inquiry is enough to review it.
            Log::info('Contact form submission stored as spam.', ['inquiry' => $inquiry->id]);
        }

        // Visitors see the same confirmation either way.
        return redirect()->route('site.contact')->with('contact_sent', true);
    }
}
