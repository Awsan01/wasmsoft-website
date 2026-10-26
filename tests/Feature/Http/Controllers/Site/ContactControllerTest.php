<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Inquiries\InquiryStatus;
use App\Models\Inquiry;
use App\Models\Service;
use App\Notifications\InquiryReceived;
use App\Security\SpamGuard;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A form as a person sends it: shown a little while ago, honeypot empty.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function submission(array $overrides = []): array
    {
        $token = app(SpamGuard::class)->token();
        $this->travel(20)->seconds();

        return [
            SpamGuard::TOKEN => $token,
            SpamGuard::HONEYPOT => '',
            'name' => 'Layla Haddad',
            'email' => 'layla@example.com',
            'phone' => '',
            'company' => '',
            'service_id' => '',
            'message' => "Hello,\n\nWe would like a quote for a booking platform.",
            'consent' => '1',
            ...$overrides,
        ];
    }

    private function configureInbox(string $address): void
    {
        app(SiteSettings::class)->put([SettingKey::InquiryNotificationEmail->value => $address]);
    }

    public function test_the_page_shows_the_form_and_only_the_details_that_are_configured(): void
    {
        $response = $this->get('/en/contact');

        $response->assertOk();
        $response->assertSee('name="form_token"', false);
        $response->assertDontSee(__('site.contact.details_title'));

        app(SiteSettings::class)->put([SettingKey::ContactEmail->value => 'hello@wasmsoft.example']);

        $this->get('/en/contact')
            ->assertSee(__('site.contact.details_title'))
            ->assertSee('href="mailto:hello@wasmsoft.example"', false);
    }

    public function test_a_message_is_stored_and_the_team_inbox_is_notified(): void
    {
        Notification::fake();
        $this->configureInbox('team@wasmsoft.example');

        $response = $this->post('/en/contact', $this->submission());

        $response->assertRedirect('/en/contact');
        $response->assertSessionHas('contact_sent', true);

        $inquiry = Inquiry::query()->sole();
        $this->assertSame(InquiryStatus::New, $inquiry->status);
        $this->assertSame('en', $inquiry->locale);
        $this->assertSame('Layla Haddad', $inquiry->name);
        $this->assertSame("Hello,\n\nWe would like a quote for a booking platform.", $inquiry->message);
        $this->assertNotNull($inquiry->consented_at);

        Notification::assertSentOnDemand(
            InquiryReceived::class,
            fn (InquiryReceived $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'team@wasmsoft.example'
                && $notification->inquiry->is($inquiry),
        );
    }

    public function test_the_confirmation_is_shown_after_sending(): void
    {
        $this->followingRedirects()->post('/ar/contact', $this->submission())
            ->assertOk()
            ->assertSee(__('site.contact.sent_title', [], 'ar'))
            ->assertDontSee('name="message"', false);
    }

    public function test_without_a_configured_inbox_no_mail_is_sent(): void
    {
        Notification::fake();

        $this->post('/en/contact', $this->submission())->assertRedirect('/en/contact');

        $this->assertSame(1, Inquiry::query()->count());
        Notification::assertNothingSent();
    }

    public function test_errors_are_listed_and_the_input_is_kept(): void
    {
        $this->from('/en/contact')
            ->followingRedirects()
            ->post('/en/contact', $this->submission(['name' => '', 'email' => 'not-an-email', 'consent' => null]))
            ->assertOk()
            ->assertSee(trans_choice('site.contact.errors_title', 3, ['count' => 3]))
            ->assertSee(__('site.contact.errors.name_required'))
            ->assertSee(__('site.contact.errors.email_invalid'))
            ->assertSee('href="#field-name"', false)
            ->assertSee('href="#field-consent"', false)
            ->assertSee('href="#field-email"', false)
            ->assertSee('value="not-an-email"', false)
            ->assertSee('We would like a quote');

        $this->assertSame(0, Inquiry::query()->count());
    }

    public function test_a_redisplayed_form_keeps_its_original_token(): void
    {
        $submission = $this->submission(['message' => 'short']);

        $this->from('/en/contact')
            ->followingRedirects()
            ->post('/en/contact', $submission)
            ->assertSee('id="field-message-error"', false)
            ->assertSee('value="'.$submission[SpamGuard::TOKEN].'"', false);
    }

    public function test_a_filled_honeypot_keeps_the_message_as_spam_without_telling_the_sender(): void
    {
        Notification::fake();
        $this->configureInbox('team@wasmsoft.example');

        $this->post('/en/contact', $this->submission([SpamGuard::HONEYPOT => 'https://spam.example']))
            ->assertRedirect('/en/contact')
            ->assertSessionHas('contact_sent', true);

        $this->assertSame(InquiryStatus::Spam, Inquiry::query()->sole()->status);
        Notification::assertNothingSent();
    }

    public function test_a_form_sent_within_seconds_is_kept_as_spam(): void
    {
        Notification::fake();
        $this->configureInbox('team@wasmsoft.example');

        $this->post('/en/contact', [...$this->submission(), SpamGuard::TOKEN => app(SpamGuard::class)->token()])
            ->assertSessionHas('contact_sent', true);

        $this->assertSame(InquiryStatus::Spam, Inquiry::query()->sole()->status);
        Notification::assertNothingSent();
    }

    public function test_forged_and_expired_forms_are_refused(): void
    {
        $this->post('/en/contact', $this->submission([SpamGuard::TOKEN => 'forged']))
            ->assertSessionHasErrors([SpamGuard::TOKEN => __('site.contact.errors.expired')]);

        $expired = $this->submission();
        $this->travel(3)->hours();

        $this->post('/en/contact', $expired)->assertSessionHasErrors(SpamGuard::TOKEN);

        $this->assertSame(0, Inquiry::query()->count());
    }

    public function test_sending_is_rate_limited(): void
    {
        config(['wasmsoft.contact.max_per_hour' => 2]);

        $this->post('/en/contact', $this->submission())->assertRedirect();
        $this->post('/en/contact', $this->submission())->assertRedirect();
        $this->post('/en/contact', $this->submission())->assertTooManyRequests();

        $this->assertSame(2, Inquiry::query()->count());
    }

    public function test_single_line_fields_are_normalized(): void
    {
        $this->post('/en/contact', $this->submission(['name' => "  Layla\r\nBcc: someone@example.com ", 'company' => "Acme\n Ltd"]));

        $inquiry = Inquiry::query()->sole();
        $this->assertSame('Layla Bcc: someone@example.com', $inquiry->name);
        $this->assertSame('Acme Ltd', $inquiry->company);
    }

    public function test_only_published_services_can_be_chosen(): void
    {
        $draft = Service::factory()->translated(['en'])->create();
        $published = Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Mobile apps']])->create();

        $this->get('/en/contact')->assertSee('Mobile apps');

        $this->post('/en/contact', $this->submission(['service_id' => (string) $draft->id]))->assertSessionHasErrors('service_id');
        $this->post('/en/contact', $this->submission(['service_id' => (string) $published->id]))->assertSessionHasNoErrors();

        $this->assertSame($published->id, Inquiry::query()->sole()->service_id);
    }

    public function test_the_page_is_never_cached_by_shared_caches(): void
    {
        $cacheControl = (string) $this->get('/en/contact')->headers->get('Cache-Control');

        $this->assertStringNotContainsString('public', $cacheControl);
    }
}
