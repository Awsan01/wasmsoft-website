<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\User;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A complete form as the page submits it, with nothing filled in but the name.
     *
     * @param  array<string, mixed>  $overrides  top-level fields to replace
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $empty = ['ar' => '', 'en' => '', 'tr' => ''];

        return [
            'site_name' => ['ar' => '', 'en' => 'Wasmsoft', 'tr' => ''],
            'site_tagline' => $empty,
            'site_description' => $empty,
            'logo_id' => '',
            'favicon_id' => '',
            'og_image_id' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => $empty,
            'contact_hours' => $empty,
            'contact_map_url' => '',
            'notification_email' => '',
            'social' => ['linkedin' => '', 'x' => '', 'facebook' => '', 'instagram' => '', 'github' => '', 'youtube' => ''],
            'enabled_locales' => ['ar', 'en', 'tr'],
            'default_locale' => 'en',
            'footer_note' => $empty,
            ...$overrides,
        ];
    }

    private function settings(): SiteSettings
    {
        $settings = app(SiteSettings::class);
        $settings->flush();

        return $settings;
    }

    public function test_an_administrator_saves_the_settings_and_the_change_is_audited(): void
    {
        $response = $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'site_name' => ['ar' => 'شركة تجريبية', 'en' => 'Wasmsoft', 'tr' => ''],
            'contact_email' => 'hello@wasmsoft.example',
            'contact_phone' => '+90 212 000 00 00',
            'contact_address' => ['ar' => '', 'en' => "Sample street 1\nIstanbul", 'tr' => ''],
            'notification_email' => 'team@wasmsoft.example',
            'social' => ['linkedin' => 'https://www.linkedin.com/company/example'],
            'enabled_locales' => ['en', 'ar'],
            'footer_note' => ['ar' => '', 'en' => 'Registered in Istanbul', 'tr' => ''],
        ]));

        $response->assertRedirect(route('admin.settings.edit'));
        $response->assertSessionHas('status');

        $settings = $this->settings();
        $this->assertSame(['ar' => 'شركة تجريبية', 'en' => 'Wasmsoft'], $settings->get(SettingKey::SiteName));
        $this->assertSame('hello@wasmsoft.example', $settings->get(SettingKey::ContactEmail));
        $this->assertSame(['en' => "Sample street 1\nIstanbul"], $settings->get(SettingKey::ContactAddress));
        $this->assertSame([['network' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example']], $settings->get(SettingKey::SocialLinks));
        // Stored in the order of the language selectors.
        $this->assertSame(['ar', 'en'], $settings->get(SettingKey::EnabledLocales));

        $log = AuditLog::query()->where('event', AuditEvent::SettingsUpdated)->sole();
        $this->assertContains('contact.email', $log->properties['changed'] ?? []);
        $this->assertStringNotContainsString('hello@wasmsoft.example', (string) json_encode($log->properties));
    }

    public function test_saving_unchanged_settings_records_nothing(): void
    {
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload());
        AuditLog::query()->delete();

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload())->assertSessionHasNoErrors();

        $this->assertSame(0, AuditLog::query()->count());
    }

    public function test_editors_cannot_open_or_change_the_settings(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.settings.update'), $this->payload(['contact_email' => 'x@example.com']))->assertForbidden();

        $this->assertNull($this->settings()->get(SettingKey::ContactEmail));
    }

    public function test_the_default_language_must_be_enabled(): void
    {
        $response = $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'enabled_locales' => ['ar'],
            'default_locale' => 'en',
        ]));

        $response->assertSessionHasErrors('enabled_locales');
    }

    public function test_the_company_needs_a_name_in_the_default_language(): void
    {
        $response = $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'site_name' => ['ar' => 'شركة تجريبية', 'en' => '', 'tr' => ''],
        ]));

        $response->assertSessionHasErrors('site_name.en');
    }

    public function test_social_links_must_be_secure_links_to_their_own_network(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload(['social' => ['linkedin' => 'https://linkedin.example.com/company']]))
            ->assertSessionHasErrors('social.linkedin');
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload(['social' => ['github' => 'http://github.com/wasmsoft']]))
            ->assertSessionHasErrors('social.github');
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload(['social' => ['x' => 'javascript:alert(1)']]))
            ->assertSessionHasErrors('social.x');
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload(['social' => ['youtube' => 'https://www.youtube.com/@wasmsoft']]))
            ->assertSessionHasNoErrors();
    }

    public function test_unknown_fields_cannot_write_other_settings(): void
    {
        $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'site_name' => ['en' => 'Wasmsoft', 'de' => 'Wasmsoft GmbH'],
        ]))->assertSessionHasErrors('site_name');
    }

    public function test_a_disabled_language_disappears_from_the_website(): void
    {
        $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'enabled_locales' => ['ar', 'en'],
        ]));

        $this->get('/tr')->assertNotFound();
        $this->get('/en')->assertOk()->assertDontSee('hreflang="tr"', false);
    }

    public function test_saved_contact_details_and_logo_appear_on_the_website(): void
    {
        $logo = Media::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $this->payload([
            'contact_email' => 'hello@wasmsoft.example',
            'logo_id' => (string) $logo->id,
        ]))->assertSessionHasNoErrors();

        $this->get('/en/contact')
            ->assertOk()
            ->assertSee('href="mailto:hello@wasmsoft.example"', false)
            ->assertSee('src="'.$logo->urlForWidth(480).'"', false);
    }

    public function test_the_form_shows_the_saved_values(): void
    {
        app(SiteSettings::class)->put([SettingKey::ContactPhone->value => '+90 212 000 00 00']);

        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('value="+90 212 000 00 00"', false)
            ->assertSee('name="site_name[ar]"', false);
    }
}
