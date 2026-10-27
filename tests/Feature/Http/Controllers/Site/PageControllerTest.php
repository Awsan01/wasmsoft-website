<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
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
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('site/pages/about')
                ->where('page.body', fn (string $body): bool => str_contains($body, 'Our mission is to build useful software.'))
                ->where('values.0.title', 'Honesty')
                ->where('team.0.name', 'Omar Haddad')
                ->where('team.0.role', 'Software engineer')
                ->where('milestones.0.year', 2019)
                ->where('milestones.0.title', 'Company founded'));

        // The Turkish version shows only what is written in Turkish.
        $this->get('/tr/hakkimizda')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('values.0.title', 'Dürüstlük')
                ->has('team', 0)
                ->has('milestones', 0));
    }

    public function test_the_partnerships_template_adds_the_ways_to_partner_written_in_the_language(): void
    {
        Page::factory()->published()->partnerships()->translated(['en', 'tr'], [
            'en' => ['title' => 'Partnerships', 'slug' => 'partnerships'],
            'tr' => ['title' => 'İş ortaklıkları', 'slug' => 'is-ortakliklari'],
        ])->create();
        Highlight::factory()->group(HighlightGroup::Partnership)->create(['title' => ['en' => 'Technology partners'], 'description' => ['en' => 'Build on our work.']]);
        Highlight::factory()->group(HighlightGroup::Value)->create(['title' => ['en' => 'Honesty']]);

        $this->get('/en/partnerships')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('site/pages/partnerships')
                ->has('models', 1)
                ->where('models.0.title', 'Technology partners')
                ->where('models.0.description', 'Build on our work.'))
            ->assertPageDoesNotShow('Honesty');

        $this->get('/tr/is-ortakliklari')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('models', 0));
    }

    public function test_the_default_template_has_no_about_sections(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => ['slug' => 'careers']])->create();
        Highlight::factory()->group(HighlightGroup::Value)->create(['title' => ['en' => 'Honesty']]);

        $this->get('/en/careers')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('site/pages/show')->missing('values'))
            ->assertPageDoesNotShow('Honesty');
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
        $response->assertSee('<title data-inertia="title">Careers at Wasmsoft · Wasmsoft</title>', false);
        $response->assertSee('<link data-inertia="canonical" rel="canonical" href="'.url('/en/careers').'">', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/pages/show')
            ->where('page.title', 'Careers at Wasmsoft')
            ->where('page.summary', 'Join the team.')
            ->where('page.body', fn (string $body): bool => str_contains($body, '<h2>Open roles</h2>')));
    }

    public function test_arabic_page_is_served_from_its_arabic_slug_right_to_left(): void
    {
        Page::factory()->published()->translated(['ar'], ['ar' => ['title' => 'من نحن', 'slug' => 'من-نحن']])->create();

        $response = $this->get('/ar/'.rawurlencode('من-نحن'));

        $response->assertOk();
        $response->assertSee('<html lang="ar" dir="rtl">', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('page.title', 'من نحن')->where('locale.dir', 'rtl'));
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
        $response->assertSee('<title data-inertia="title">'.e(__('site.missing.title', [], 'tr')).' · Wasmsoft</title>', false);
        $response->assertSee('<meta data-inertia="robots" name="robots" content="noindex, nofollow">', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/translation-missing')
            ->has('available', 1)
            ->where('available.0.code', 'en')
            ->where('available.0.url', url('/en/privacy')));
        $response->assertPageDoesNotShow(url('/ar/privacy'), 'English-only body text.');
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

        $response->assertSee('<link data-inertia="alternate-ar" rel="alternate" hreflang="ar" href="'.url('/ar/'.rawurlencode('نظرة-عامة')).'">', false);
        $response->assertDontSee('hreflang="tr"', false);
        // Turkish has no translation: the switcher offers the Turkish home page instead.
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('languages.0.code', 'ar')
            ->where('languages.0.url', url('/ar/'.rawurlencode('نظرة-عامة')))
            ->where('languages.2.code', 'tr')
            ->where('languages.2.url', url('/tr')));
    }

    public function test_markdown_cannot_inject_html_or_script_links(): void
    {
        Page::factory()->published()->translated(['en'], ['en' => [
            'slug' => 'unsafe',
            'body' => "<script>alert('x')</script>\n\n[Click me](javascript:alert(1))",
        ]])->create();

        $response = $this->get('/en/unsafe');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('page.body', fn (string $body): bool => str_contains($body, '&lt;script&gt;')
            && ! str_contains($body, '<script>')
            && ! str_contains($body, 'javascript:')));
    }
}
