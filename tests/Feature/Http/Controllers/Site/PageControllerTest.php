<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_about_template_adds_values_team_and_history_written_in_the_language(): void
    {
        Page::factory()->published()->about()->translated(['en', 'tr'], [
            'en' => ['title' => 'About us', 'slug' => 'about', 'body' => 'Our mission is to build useful software.'],
            'tr' => ['title' => 'Hakkımızda', 'slug' => 'hakkimizda'],
        ])->create();
        Highlight::factory()->group(HighlightGroup::Value)->create(['title' => ['en' => 'Honesty', 'tr' => 'Dürüstlük']]);
        TeamMember::factory()->create(['name' => 'Omar Haddad', 'role' => ['en' => 'Software engineer']]);
        Milestone::factory()->create(['year' => 2019, 'title' => ['en' => 'Company founded']]);

        $this->get('/en/about')
            ->assertOk()
            ->assertSee('Our mission is to build useful software.')
            ->assertSeeInOrder([__('site.about.values_title'), 'Honesty', __('site.about.team_title'), 'Omar Haddad', 'Software engineer', __('site.about.history_title'), '2019', 'Company founded']);

        // The Turkish version shows only what is written in Turkish.
        $this->get('/tr/hakkimizda')
            ->assertOk()
            ->assertSee('Dürüstlük')
            ->assertDontSee('Omar Haddad')
            ->assertDontSee('Company founded');
    }

    public function test_the_default_template_has_no_about_sections(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => ['slug' => 'careers']])->create();
        Highlight::factory()->group(HighlightGroup::Value)->create(['title' => ['en' => 'Honesty']]);

        $this->get('/en/careers')->assertOk()->assertDontSee('Honesty');
    }

    public function test_published_page_renders_in_its_language(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => [
            'title' => 'Careers at Wasmsoft',
            'slug' => 'careers',
            'summary' => 'Join the team.',
            'body' => "## Open roles\n\nWe are hiring.",
        ]])->create();

        $response = $this->get('/en/careers');

        $response->assertOk();
        $response->assertSee('<html lang="en" dir="ltr">', false);
        $response->assertSee('<title>Careers at Wasmsoft · Wasmsoft</title>', false);
        $response->assertSee('<h2>Open roles</h2>', false);
        $response->assertSee('<link rel="canonical" href="'.url('/en/careers').'">', false);
    }

    public function test_arabic_page_is_served_from_its_arabic_slug_right_to_left(): void
    {
        Page::factory()->published()->translated(['ar'], ['ar' => ['title' => 'من نحن', 'slug' => 'من-نحن']])->create();

        $response = $this->get('/ar/'.rawurlencode('من-نحن'));

        $response->assertOk();
        $response->assertSee('<html lang="ar" dir="rtl">', false);
        $response->assertSee('من نحن');
    }

    public function test_unpublished_pages_are_not_found(): void
    {
        Page::factory()->translated(['en'], ['en' => ['slug' => 'draft-page']])->create();
        Page::factory()->scheduled()->translated(['en'], ['en' => ['slug' => 'scheduled-page']])->create();
        Page::factory()->archived()->translated(['en'], ['en' => ['slug' => 'archived-page']])->create();

        $this->get('/en/draft-page')->assertNotFound();
        $this->get('/en/scheduled-page')->assertNotFound();
        $this->get('/en/archived-page')->assertNotFound();
    }

    public function test_slug_from_another_language_redirects_to_this_languages_version(): void
    {
        Page::factory()->published()->translated(['en', 'tr'], [
            'en' => ['title' => 'About us', 'slug' => 'about-us'],
            'tr' => ['title' => 'Hakkımızda', 'slug' => 'hakkimizda'],
        ])->create();

        $response = $this->get('/tr/about-us');

        $response->assertMovedPermanently();
        $response->assertRedirect('/tr/hakkimizda');
    }

    public function test_missing_translation_answers_404_with_links_to_existing_languages_only(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => [
            'title' => 'Privacy policy',
            'slug' => 'privacy',
            'body' => 'English-only body text.',
        ]])->create();

        $response = $this->get('/tr/privacy');

        $response->assertNotFound();
        $response->assertSee('<html lang="tr" dir="ltr">', false);
        $response->assertSee(__('site.missing.title', [], 'tr'));
        $response->assertSee('href="'.url('/en/privacy').'"', false);
        $response->assertDontSee('href="'.url('/ar/privacy').'"', false);
        $response->assertDontSee('English-only body text.');
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_unknown_slug_is_not_found(): void
    {
        $this->get('/en/does-not-exist')->assertNotFound();
    }

    public function test_hreflang_and_language_switcher_only_offer_existing_translations(): void
    {
        Page::factory()->published()->translated(['en', 'ar'], [
            'en' => ['slug' => 'services-overview'],
            'ar' => ['slug' => 'نظرة-عامة'],
        ])->create();

        $response = $this->get('/en/services-overview');

        $response->assertSee('<link rel="alternate" hreflang="ar" href="'.url('/ar/'.rawurlencode('نظرة-عامة')).'">', false);
        $response->assertDontSee('hreflang="tr" href="'.url('/tr/'), false);
        // Turkish has no translation: the switcher offers the Turkish home page instead.
        $response->assertSee('href="'.url('/tr').'"', false);
        $response->assertDontSee(url('/tr/services-overview'), false);
    }

    public function test_markdown_cannot_inject_html_or_script_links(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => [
            'slug' => 'unsafe',
            'body' => "<script>alert('x')</script>\n\n[Click me](javascript:alert(1))",
        ]])->create();

        $response = $this->get('/en/unsafe');

        $response->assertOk();
        $response->assertDontSee("<script>alert('x')</script>", false);
        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee('javascript:alert(1)', false);
    }
}
