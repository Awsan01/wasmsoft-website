<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class LocaleRedirectControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['ar-SA,ar;q=0.9', 'ar'])]
    #[TestWith(['tr-TR,tr;q=0.9,en;q=0.8', 'tr'])]
    #[TestWith(['de-DE,fr;q=0.8', 'en'])]
    public function test_root_redirects_to_the_best_enabled_language(string $acceptLanguage, string $expected): void
    {
        $response = $this->get('/', ['Accept-Language' => $acceptLanguage]);

        $response->assertRedirect("/{$expected}");
        $response->assertHeader('Vary', 'Accept-Language');
    }

    public function test_disabled_language_is_not_chosen_even_when_preferred(): void
    {
        app(SiteSettings::class)->put([SettingKey::EnabledLocales->value => ['en', 'tr']]);

        $response = $this->get('/', ['Accept-Language' => 'ar']);

        $response->assertRedirect('/en');
    }
}
