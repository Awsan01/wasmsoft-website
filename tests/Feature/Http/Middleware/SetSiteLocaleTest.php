<?php

namespace Tests\Feature\Http\Middleware;

use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
    }

    #[TestWith(['ar', 'الخدمات', 'Services'])]
    #[TestWith(['tr', 'Hizmetler', 'Services'])]
    #[TestWith(['en', 'Services', 'Hizmetler'])]
    public function test_interface_strings_follow_the_page_language(string $locale, string $expected, string $otherLanguage): void
    {
        $response = $this->get("/{$locale}");

        $response->assertSee(">{$expected}</a>", false);
        $response->assertDontSee(">{$otherLanguage}</a>", false);
    }

    public function test_generated_links_stay_in_the_current_language(): void
    {
        $response = $this->get('/tr');

        $response->assertSee('href="'.url('/tr/services').'"', false);
        $response->assertDontSee('href="'.url('/en/services').'"', false);
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
