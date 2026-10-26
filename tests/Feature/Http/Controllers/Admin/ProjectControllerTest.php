<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Auth\Role;
use App\Content\PublicationStatus;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, array<string, string>>  $translations
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $translations = [], array $overrides = []): array
    {
        $empty = ['title' => '', 'slug' => '', 'summary' => '', 'body' => '', 'meta_title' => '', 'meta_description' => ''];

        return [
            'status' => PublicationStatus::Draft->value,
            'project_category_id' => '',
            'cover_id' => '',
            'client_name' => '',
            'project_url' => '',
            'completed_on' => '',
            'technologies' => '',
            'is_featured' => '0',
            'translations' => [
                'ar' => [...$empty, ...($translations['ar'] ?? [])],
                'en' => [...$empty, ...($translations['en'] ?? [])],
                'tr' => [...$empty, ...($translations['tr'] ?? [])],
            ],
            ...$overrides,
        ];
    }

    public function test_editor_creates_a_complete_project(): void
    {
        $category = ProjectCategory::factory()->translated(['en'])->create();
        $cover = Media::factory()->create();
        [$first, $second] = Media::factory()->count(2)->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.projects.store'), $this->payload(
            ['en' => ['title' => 'Logistics platform', 'summary' => 'Real-time fleet tracking.']],
            [
                'status' => PublicationStatus::Published->value,
                'project_category_id' => (string) $category->id,
                'cover_id' => (string) $cover->id,
                'gallery' => [(string) $second->id, (string) $first->id],
                'client_name' => 'Acme Logistics',
                'project_url' => 'https://acme.example',
                'completed_on' => '2026-03-15',
                'technologies' => 'Laravel,  Vue.js , ,PostgreSQL',
                'is_featured' => '1',
            ],
        ));

        $project = Project::query()->with(['gallery', 'translations'])->sole();
        $response->assertRedirect(route('admin.projects.edit', $project));
        $this->assertSame($category->id, $project->project_category_id);
        $this->assertSame($cover->id, $project->cover_id);
        $this->assertSame([$second->id, $first->id], $project->gallery->modelKeys());
        $this->assertSame(['Laravel', 'Vue.js', 'PostgreSQL'], $project->technologies);
        $this->assertSame('Acme Logistics', $project->client_name);
        $this->assertSame('2026-03-15', $project->completed_on?->toDateString());
        $this->assertTrue($project->is_featured);
        $this->assertSame('logistics-platform', $project->translation('en')?->slug);
    }

    public function test_duplicate_technologies_are_rejected_regardless_of_case(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.projects.store'), $this->payload(
            ['en' => ['title' => 'App']],
            ['technologies' => 'Laravel, laravel'],
        ));

        $response->assertSessionHasErrors('technologies.1');
    }

    #[TestWith(['javascript:alert(1)'])]
    #[TestWith(['ftp://files.example.com'])]
    #[TestWith(['not a url'])]
    public function test_project_url_must_be_a_web_address(string $url): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.projects.store'), $this->payload(
            ['en' => ['title' => 'App']],
            ['project_url' => $url],
        ));

        $response->assertSessionHasErrors('project_url');
    }

    public function test_gallery_accepts_only_distinct_images(): void
    {
        $image = Media::factory()->create();
        $document = Media::factory()->document()->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.projects.store'), $this->payload(
            ['en' => ['title' => 'App']],
            ['gallery' => [(string) $image->id, (string) $image->id, (string) $document->id]],
        ));

        $response->assertSessionHasErrors(['gallery.0', 'gallery.2']);
        $this->assertSame(0, Project::query()->count());
    }

    public function test_updating_replaces_the_gallery_in_the_new_order(): void
    {
        [$a, $b, $c] = Media::factory()->count(3)->create();
        $project = Project::factory()->translated(['en'], ['en' => ['title' => 'Portal']])->create();
        $project->gallery()->sync([$a->id => ['position' => 1], $b->id => ['position' => 2]]);

        $this->actingAs(User::factory()->editor()->create())->put(route('admin.projects.update', $project), $this->payload(
            ['en' => ['title' => 'Portal']],
            ['gallery' => [(string) $c->id, (string) $a->id]],
        ));

        $this->assertSame([$c->id, $a->id], $project->fresh()?->gallery->modelKeys());
    }

    public function test_after_a_failed_submission_the_chosen_gallery_is_shown_again(): void
    {
        $editor = User::factory()->editor()->create();
        $first = Media::factory()->create(['original_name' => 'first.jpg']);
        $second = Media::factory()->create(['original_name' => 'second.jpg']);

        $this->actingAs($editor)
            ->from(route('admin.projects.create'))
            ->post(route('admin.projects.store'), $this->payload([], ['gallery' => [(string) $second->id, (string) $first->id]]))
            ->assertSessionHasErrors();

        $response = $this->actingAs($editor)->get(route('admin.projects.create'));

        $response->assertSeeInOrder([
            'name="gallery[]" value="'.$second->id.'"',
            'name="gallery[]" value="'.$first->id.'"',
        ], false);
    }

    public function test_gallery_images_cannot_be_deleted_from_the_library(): void
    {
        $image = Media::factory()->create();
        $project = Project::factory()->translated(['en'])->create();
        $project->gallery()->attach($image->id, ['position' => 1]);

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.media.destroy', $image));

        $this->assertModelExists($image);
    }

    public function test_authors_cannot_manage_projects(): void
    {
        $author = User::factory()->role(Role::Author)->create();

        $this->actingAs($author)->post(route('admin.projects.store'), $this->payload(['en' => ['title' => 'App']]))->assertForbidden();
        $this->actingAs($author)->get(route('admin.projects.index'))->assertOk();
    }

    public function test_preview_renders_a_draft_project(): void
    {
        $project = Project::factory()->translated(['en'], ['en' => ['title' => 'Unreleased portal']])->create(['client_name' => 'Northwind']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.projects.preview', [$project, 'en']));

        $response->assertOk();
        $response->assertSee('Unreleased portal');
        $response->assertSee('Northwind');
        $response->assertSee(__('site.preview.banner'));
    }
}
