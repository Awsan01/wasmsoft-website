<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Content\HighlightGroup;
use App\Content\SystemPage;
use App\Models\Highlight;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_without_content_the_page_shows_the_hero_and_no_empty_sections(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee(__('site.home.hero_title'));

        // (Section names such as "Services" also appear in the navigation.)
        foreach (['home-services', 'home-why', 'home-figures', 'home-projects', 'home-testimonials', 'home-news'] as $section) {
            $response->assertDontSee('id="'.$section.'"', false);
        }
    }

    public function test_the_hero_uses_the_home_page_written_in_the_dashboard(): void
    {
        Page::factory()->system(SystemPage::Home)->translated(['en'], ['en' => ['title' => 'Apps that ship', 'summary' => 'From idea to launch.']])->create();

        $this->get('/en')
            ->assertSee('Apps that ship')
            ->assertSee('From idea to launch.')
            ->assertDontSee(__('site.home.hero_title'));
    }

    public function test_featured_services_and_projects_come_first(): void
    {
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Regular service']])->create();
        Service::factory()->published()->featured()->translated(['en'], ['en' => ['title' => 'Featured service']])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Recent project']])->create();
        Project::factory()->published()->featured()->translated(['en'], ['en' => ['title' => 'Featured project']])->create(['published_at' => now()->subYear()]);

        $this->get('/en')
            ->assertSeeInOrder(['Featured service', 'Regular service'])
            ->assertSeeInOrder(['Featured project', 'Recent project']);
    }

    public function test_highlights_and_figures_appear_only_in_the_languages_they_are_written_in(): void
    {
        Highlight::factory()->create(['title' => ['en' => 'Clear communication']]);
        Highlight::factory()->group(HighlightGroup::Metric)->create(['figure' => '12', 'title' => ['en' => 'Years in business']]);
        Highlight::factory()->hidden()->create(['title' => ['en' => 'Hidden reason']]);

        $this->get('/en')
            ->assertSee(__('site.home.why_title'))
            ->assertSee('Clear communication')
            ->assertSeeInOrder(['Years in business', '12'])
            ->assertDontSee('Hidden reason');

        $this->get('/ar')
            ->assertDontSee('Clear communication')
            ->assertDontSee(__('site.home.why_title', [], 'ar'));
    }

    public function test_only_consented_testimonials_are_quoted(): void
    {
        Testimonial::factory()->shown()->create(['quote' => ['en' => 'A team you can rely on.'], 'author_name' => 'Selin Kaya']);
        // Visible but without consent: possible only by bypassing the form, still never shown.
        Testimonial::factory()->create(['quote' => ['en' => 'Unapproved words.'], 'is_visible' => true, 'has_consent' => false]);

        $this->get('/en')
            ->assertSee('A team you can rely on.')
            ->assertSee('Selin Kaya')
            ->assertDontSee('Unapproved words.');
    }

    public function test_the_organization_is_described_with_the_configured_details_only(): void
    {
        app(SiteSettings::class)->put([
            SettingKey::ContactEmail->value => 'hello@wasmsoft.example',
            SettingKey::SocialLinks->value => [['network' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example']],
        ]);

        $this->get('/en')
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"email":"hello@wasmsoft.example"', false)
            ->assertSee('"sameAs":["https://www.linkedin.com/company/example"]', false)
            ->assertDontSee('"telephone"', false)
            ->assertSee('"@type":"WebSite"', false);
    }
}
