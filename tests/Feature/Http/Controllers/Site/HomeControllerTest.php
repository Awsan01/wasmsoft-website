<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Content\HighlightGroup;
use App\Content\SystemPage;
use App\Http\Requests\Site\ContactRequest;
use App\Models\Highlight;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Security\SpamGuard;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_without_content_the_page_shows_the_hero_and_no_empty_sections(): void
    {
        // Each section of the page is left out while its list is empty.
        $this->get('/en')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('site/home')
                ->where('hero.title', __('site.home.hero_title'))
                ->where('hero.lead', __('site.home.hero_lead'))
                ->where('hero.tagline', null)
                ->where('intro', '')
                ->has('services', 0)
                ->has('projects', 0)
                ->has('posts', 0)
                ->has('differentiators', 0)
                ->has('figures', 0)
                ->has('testimonials', 0));
    }

    public function test_the_hero_uses_the_home_page_written_in_the_dashboard(): void
    {
        Page::factory()->system(SystemPage::Home)->translated(['en'], ['en' => ['title' => 'Apps that ship', 'summary' => 'From idea to launch.']])->create();

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hero.title', 'Apps that ship')
            ->where('hero.lead', 'From idea to launch.'));
    }

    public function test_featured_services_and_projects_come_first(): void
    {
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Regular service']])->create();
        Service::factory()->published()->featured()->translated(['en'], ['en' => ['title' => 'Featured service']])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Recent project']])->create();
        Project::factory()->published()->featured()->translated(['en'], ['en' => ['title' => 'Featured project']])->create(['published_at' => now()->subYear()]);

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('services.0.title', 'Featured service')
            ->where('services.1.title', 'Regular service')
            ->where('projects.0.title', 'Featured project')
            ->where('projects.1.title', 'Recent project'));
    }

    public function test_the_page_shows_at_most_six_projects_and_three_articles(): void
    {
        Project::factory()->count(7)->published()->translated(['en'])->create();
        Post::factory()->count(4)->published()->translated(['en'])->create();

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page->has('projects', 6)->has('posts', 3));
    }

    public function test_highlights_and_figures_appear_only_in_the_languages_they_are_written_in(): void
    {
        Highlight::factory()->create(['title' => ['en' => 'Clear communication']]);
        Highlight::factory()->group(HighlightGroup::Metric)->create(['figure' => '12', 'title' => ['en' => 'Years in business']]);
        Highlight::factory()->hidden()->create(['title' => ['en' => 'Hidden reason']]);

        $this->get('/en')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('differentiators', 1)
                ->where('differentiators.0.title', 'Clear communication')
                ->where('figures.0.title', 'Years in business')
                ->where('figures.0.figure', '12'))
            ->assertPageDoesNotShow('Hidden reason');

        $this->get('/ar')->assertInertia(fn (AssertableInertia $page) => $page->has('differentiators', 0)->has('figures', 0));
    }

    public function test_only_consented_testimonials_are_quoted(): void
    {
        Testimonial::factory()->shown()->create(['quote' => ['en' => 'A team you can rely on.'], 'author_name' => 'Selin Kaya']);
        // Visible but without consent: possible only by bypassing the form, still never shown.
        Testimonial::factory()->create(['quote' => ['en' => 'Unapproved words.'], 'is_visible' => true, 'has_consent' => false]);

        $this->get('/en')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials', 1)
                ->where('testimonials.0.quote', 'A team you can rely on.')
                ->where('testimonials.0.author', 'Selin Kaya'))
            ->assertPageDoesNotShow('Unapproved words.');
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

    public function test_the_page_ends_with_the_contact_form_without_starting_a_session(): void
    {
        $second = Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Mobile apps']])->create(['position' => 2]);
        $first = Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Web apps']])->create(['position' => 1]);
        Service::factory()->published()->translated(['tr'], ['tr' => ['title' => 'Yalnızca Türkçe']])->create();
        Service::factory()->translated(['en'], ['en' => ['title' => 'Draft service']])->create();

        $response = $this->get('/en');

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('contactForm.action', url('/en/contact'))
            ->where('contactForm.tokenUrl', url('/en/contact/token'))
            ->where('contactForm.tokenField', SpamGuard::TOKEN)
            ->where('contactForm.honeypotField', SpamGuard::HONEYPOT)
            ->where('contactForm.messageMax', ContactRequest::MESSAGE_MAX)
            // Every published service in this language can be the topic, in their order.
            ->where('contactForm.topics', [
                ['value' => (string) $first->id, 'label' => 'Web apps'],
                ['value' => (string) $second->id, 'label' => 'Mobile apps'],
            ]));

        // No token and no session: the page stays the same for every visitor, so it can be cached.
        $response->assertHeader('Cache-Control', 'max-age=0, public, s-maxage=300');
        $this->assertSame([], $response->headers->getCookies());
        $this->assertStringNotContainsString('"'.SpamGuard::TOKEN.'":"', (string) $response->getContent());
    }
}
