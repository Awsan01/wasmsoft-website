<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\PreparesContactForm;
use App\Http\Controllers\Site\Concerns\RendersSitePages;
use App\Http\Requests\Site\ContactRequest;
use App\Inquiries\InquiryStatus;
use App\Localization\Locales;
use App\Models\Inquiry;
use App\Models\Page;
use App\Notifications\InquiryReceived;
use App\Security\SpamGuard;
use App\Seo\SeoMeta;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Response;

/**
 * The contact page and its form (session-backed routes: CSRF, validation
 * errors). The contact details themselves reach the page with the shared
 * footer data.
 *
 * The same form also ends the home page, which has no session so that it
 * can be cached. There the form asks for its token (token()) when a visitor
 * starts filling it in, and is sent as JSON.
 */
class ContactController extends Controller
{
    use PreparesContactForm;
    use RendersSitePages;

    public function __construct(
        private readonly SiteSettings $settings,
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SpamGuard $guard,
    ) {}

    public function show(Request $request): Response
    {
        $locale = app()->getLocale();
        $intro = Page::query()->system(SystemPage::Contact)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.contact.title');

        $services = $this->contactTopics($locale);

        // A service page links here with ?service={id}: that topic is chosen in advance.
        $requested = $request->string('service')->toString();
        $selected = collect($services)->firstWhere('value', $requested) !== null ? $requested : '';

        return $this->sitePage('site/contact', [
            'title' => $title,
            'lead' => $intro?->summary ?: __('site.contact.lead'),
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.contact'), 'url' => null],
            ],
            'services' => $services,
            'form' => [
                ...$this->contactFormFields(),
                'token' => $this->guard->tokenFor(old(SpamGuard::TOKEN)),
                'service' => $selected,
            ],
            'sent' => (bool) $request->session()->get('contact_sent', false),
        ], new SeoMeta(
            title: ($intro?->meta_title ?: $title).' · '.$this->brand->name(),
            description: $intro?->meta_description ?: Markdown::toText($intro?->summary) ?: __('site.contact.lead'),
            canonical: route('site.contact'),
            alternates: Alternates::route('site.contact', $this->locales),
        ));
    }

    /**
     * A new spam-guard token for the form on the home page, which itself
     * stays without a session. Asking for it starts the visitor's session:
     * browsers that do not say where a request comes from (Sec-Fetch-Site)
     * need its CSRF cookie to send the form.
     */
    public function token(): JsonResponse
    {
        return response()->json(['token' => $this->guard->token()]);
    }

    public function store(ContactRequest $request): RedirectResponse|JsonResponse
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

        // Visitors see the same confirmation either way: in place on the home
        // page (JSON), on the contact page after it reloads.
        if ($request->expectsJson()) {
            return response()->json(['sent' => true]);
        }

        return redirect()->route('site.contact')->with('contact_sent', true);
    }
}
