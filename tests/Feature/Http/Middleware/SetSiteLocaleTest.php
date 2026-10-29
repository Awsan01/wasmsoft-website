<?php

namespace Tests\Feature\Http\Middleware;

use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SetSiteLocaleTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['ar', 'rtl'])]
    #[TestWith(['en', 'ltr'])]
    #[TestWith(['tr', 'ltr'])]
    public function test_page_declares_the_language_and_direction_of_its_url(string $locale, string $direction): void
    {
        $response = $this->get("/{$locale}");

        $response->assertOk();
        $response->assertSee("<html lang=\"{$locale}\" dir=\"{$direction}\">", false);
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('locale.code', $locale)->where('locale.dir', $direction));
    }

    #[TestWith(['ar', 'الخدمات', 'Services'])]
    #[TestWith(['tr', 'Hizmetler', 'Services'])]
    #[TestWith(['en', 'Services', 'Hizmetler'])]
    public function test_interface_strings_follow_the_page_language(string $locale, string $expected, string $otherLanguage): void
    {
        $response = $this->get("/{$locale}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            // The menu and the interface strings the page translates in the browser.
            ->where('navigation.header.1.label', $expected)
            ->where('translations.site.nav.services', $expected));
        $this->assertNotSame($otherLanguage, $response->inertiaProps('translations.site.nav.services'));
    }

    public function test_generated_links_stay_in_the_current_language(): void
    {
        $response = $this->get('/tr');

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('navigation.header.1.url', url('/tr/services'))
            ->where('site.urls.contact', url('/tr/contact')));
        $this->assertStringNotContainsString(url('/en/services'), (string) json_encode($response->inertiaPage()['props'], JSON_UNESCAPED_SLASHES));
    }

    public function test_disabled_language_returns_not_found(): void
    {
        app(SiteSettings::class)->put([SettingKey::EnabledLocales->value => ['en']]);

        $response = $this->get('/ar');

        $response->assertNotFound();
    }

    public function test_unknown_language_prefix_returns_not_found(): void
    {
        $response = $this->get('/fr');

        $response->assertNotFound();
    }
}
