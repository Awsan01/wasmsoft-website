<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Covers the shared vocabulary management (TaxonomyController) through
 * project categories.
 */
class ProjectCategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, array<string, string>>  $translations
     * @return array<string, mixed>
     */
    private function payload(array $translations): array
    {
        $empty = ['name' => '', 'slug' => ''];

        return ['translations' => [
            'ar' => [...$empty, ...($translations['ar'] ?? [])],
            'en' => [...$empty, ...($translations['en'] ?? [])],
            'tr' => [...$empty, ...($translations['tr'] ?? [])],
        ]];
    }

    public function test_editor_creates_a_category_in_several_languages(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.project-categories.store'), $this->payload([
            'en' => ['name' => 'Web applications'],
            'tr' => ['name' => 'Web uygulamaları'],
        ]));

        $category = ProjectCategory::query()->with('translations')->sole();
        $response->assertRedirect(route('admin.project-categories.edit', $category));
        $this->assertSame('web-applications', $category->translation('en')?->slug);
        $this->assertSame('web-uygulamalari', $category->translation('tr')?->slug);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentCreated->value, 'subject_type' => 'project_category']);
    }

    public function test_a_name_is_required_in_at_least_one_language(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.project-categories.store'), $this->payload([]));

        $response->assertSessionHasErrors('translations.en.name');
    }

    public function test_index_shows_how_many_projects_use_each_category(): void
    {
        $category = ProjectCategory::factory()->translated(['en'], ['en' => ['name' => 'Mobile']])->create();
        Project::factory()->count(2)->translated(['en'])->create(['project_category_id' => $category->id]);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.project-categories.index'));

        $response->assertOk();
        $response->assertSee('Mobile');
        $response->assertSee(trans_choice('admin.project_categories.usage', 2, ['count' => 2]));
    }

    public function test_deleting_a_category_keeps_its_projects(): void
    {
        $category = ProjectCategory::factory()->translated(['en'])->create();
        $project = Project::factory()->translated(['en'])->create(['project_category_id' => $category->id]);

        $this->actingAs(User::factory()->editor()->create())
            ->delete(route('admin.project-categories.destroy', $category))
            ->assertRedirect(route('admin.project-categories.index'));

        $this->assertModelMissing($category);
        $this->assertNull($project->fresh()?->project_category_id);
    }

    public function test_categories_can_be_reordered(): void
    {
        [$first, $second] = ProjectCategory::factory()->count(2)->translated(['en'])->create();

        $this->actingAs(User::factory()->editor()->create())->patch(route('admin.project-categories.move', $second), ['direction' => 'up']);

        $this->assertSame([$second->id, $first->id], ProjectCategory::query()->ordered()->pluck('id')->all());
    }

    public function test_viewers_cannot_change_categories(): void
    {
        $viewer = User::factory()->role(Role::Viewer)->create();
        $category = ProjectCategory::factory()->translated(['en'])->create();

        $this->actingAs($viewer)->post(route('admin.project-categories.store'), $this->payload(['en' => ['name' => 'X']]))->assertForbidden();
        $this->actingAs($viewer)->put(route('admin.project-categories.update', $category), $this->payload(['en' => ['name' => 'X']]))->assertForbidden();
        $this->actingAs($viewer)->delete(route('admin.project-categories.destroy', $category))->assertForbidden();
        $this->actingAs($viewer)->patch(route('admin.project-categories.move', $category), ['direction' => 'up'])->assertForbidden();
    }

    public function test_unknown_category_is_not_found(): void
    {
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.project-categories.edit', 999))->assertNotFound();
    }
}
