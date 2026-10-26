<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
        $response->assertSeeInOrder(['Newer project', 'Older project']);
        $response->assertDontSee('Unpublished project');
        $response->assertDontSee('مشروع عربي فقط');
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
        $response->assertSee('Shop website');
        $response->assertDontSee('Delivery app');
        $response->assertSee('Mobile');
        $response->assertDontSee('Empty category');
        $response->assertSee('<link rel="canonical" href="'.url('/en/projects?category=web').'">', false);
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
        $response->assertDontSee('Analytics dashboard');
        $response->assertSee('q=booking&amp;page=2', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
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
        $response->assertSee('Acme Logistics');
        $response->assertSeeInOrder(['Laravel', 'Vue.js']);
        $response->assertSee('alt="Dashboard screen"', false);
        $response->assertSee('href="https://fleet.example"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
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
    }

    public function test_related_projects_come_from_the_same_category(): void
    {
        $category = ProjectCategory::factory()->translated(['en'])->create();
        $other = ProjectCategory::factory()->translated(['en'])->create();
        Project::factory()->published()->translated(['en'], ['en' => ['slug' => 'main']])->create(['project_category_id' => $category->id]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Same category project']])->create(['project_category_id' => $category->id]);
        Project::factory()->published()->translated(['en'], ['en' => ['title' => 'Other category project']])->create(['project_category_id' => $other->id]);

        $response = $this->get('/en/projects/main');

        $response->assertSee('Same category project');
        $response->assertDontSee('Other category project');
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
