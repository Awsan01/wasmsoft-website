<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_published_projects_of_this_language_newest_first(): void
    {
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Older project']])->create(['published_at' => now()->subWeek()]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Newer project']])->create(['published_at' => now()->subDay()]);
        Project::factory()->translated(['en'], ['en' => ['title' => 'Unpublished project']])->create();
        Project::factory()->published()->translated(['ar'], ['ar' => ['title' => 'مشروع عربي فقط']])->create();

        $response = $this->get('/en/projects');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/projects/index')
            ->has('projects', 2)
            ->where('projects.0.title', 'Newer project')
            ->where('projects.1.title', 'Older project'));
        $response->assertPageDoesNotShow('Unpublished project', 'مشروع عربي فقط');
    }

    public function test_category_filter_shows_only_that_category_and_only_used_categories_are_offered(): void
    {
        $web = ProjectCategory::factory()->translated(['en'], ['en' => ['name' => 'Web', 'slug' => 'web']])->create();
        $mobile = ProjectCategory::factory()->translated(['en'], ['en' => ['name' => 'Mobile', 'slug' => 'mobile']])->create();
        ProjectCategory::factory()->translated(['en'], ['en' => ['name' => 'Empty category', 'slug' => 'empty']])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Shop website']])->create(['project_category_id' => $web->id]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Delivery app']])->create(['project_category_id' => $mobile->id]);

        $response = $this->get('/en/projects?category=web');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('projects', 1)
            ->where('projects.0.title', 'Shop website')
            ->where('search.category', 'web')
            ->where('filtered', true)
            // "All", then the categories with published projects.
            ->has('filters', 3)
            ->where('filters.0.current', false)
            ->where('filters.1.label', 'Web')
            ->where('filters.1.current', true)
            ->where('filters.2.label', 'Mobile')
            ->where('filters.2.url', url('/en/projects?category=mobile')));
        $response->assertPageDoesNotShow('Delivery app', 'Empty category');
        $response->assertSee('<link data-inertia="canonical" rel="canonical" href="'.url('/en/projects?category=web').'">', false);
    }

    public function test_unknown_category_is_not_found(): void
    {
        $this->get('/en/projects?category=does-not-exist')->assertNotFound();
    }

    public function test_search_matches_this_language_keeps_filters_in_pagination_and_is_not_indexed(): void
    {
        config(['wasmsoft.pagination.site' => 1]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Booking platform one']])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Booking platform two']])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Analytics dashboard']])->create();

        $response = $this->get('/en/projects?q=booking');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('search.value', 'booking')
            ->where('pagination.total', 2)
            ->where('pagination.nextUrl', url('/en/projects?q=booking&page=2')));
        $response->assertPageDoesNotShow('Analytics dashboard');
        $response->assertSee('<meta data-inertia="robots" name="robots" content="noindex, nofollow">', false);
    }

    public function test_project_page_shows_details_gallery_and_an_external_link(): void
    {
        $image = Media::factory()->create(['alt' => ['en' => 'Dashboard screen']]);
        $project = Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Fleet portal', 'slug' => 'fleet-portal']])->create([
            'client_name' => 'Acme Logistics',
            'technologies' => ['Laravel', 'Vue.js'],
            'project_url' => 'https://fleet.example',
        ]);
        $project->gallery()->attach($image->id, ['position' => 1]);

        $response = $this->get('/en/projects/fleet-portal');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/projects/show')
            ->where('project.client', 'Acme Logistics')
            ->where('project.technologies', ['Laravel', 'Vue.js'])
            ->where('project.gallery.0.image.alt', 'Dashboard screen')
            ->where('project.gallery.0.url', $image->url())
            // Rendered as a link that opens in a new tab with rel="noopener noreferrer" (see the browser tests).
            ->where('project.website', 'https://fleet.example'));
    }

    public function test_editor_entered_text_is_escaped(): void
    {
        Project::factory()->published()->translated(['en'], ['en' => ['slug' => 'escaped']])->create([
            'client_name' => '<script>alert(1)</script>',
            'technologies' => ['<b>bold</b>'],
        ]);

        $response = $this->get('/en/projects/escaped');

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('<b>bold</b>', false);
        // Plain text in the page data; React escapes it when rendering.
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('project.client', '<script>alert(1)</script>')
            ->where('project.technologies', ['<b>bold</b>']));
    }

    public function test_related_projects_come_from_the_same_category(): void
    {
        $category = ProjectCategory::factory()->translated(['en'])->create();
        $other = ProjectCategory::factory()->translated(['en'])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['slug' => 'main']])->create(['project_category_id' => $category->id]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Same category project']])->create(['project_category_id' => $category->id]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Other category project']])->create(['project_category_id' => $other->id]);

        $response = $this->get('/en/projects/main');

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('related', 1)->where('related.0.title', 'Same category project'));
        $response->assertPageDoesNotShow('Other category project');
    }

    public function test_slug_of_another_language_redirects(): void
    {
        Project::factory()->published()->translated(['en', 'ar'], [
            'en' => ['slug' => 'fleet-portal'],
            'ar' => ['slug' => 'بوابة-الأسطول'],
        ])->create();

        $this->get('/ar/projects/fleet-portal')->assertRedirect('/ar/projects/'.rawurlencode('بوابة-الأسطول'));
    }
}
