<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MilestoneControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_adds_a_milestone_and_the_list_follows_the_years(): void
    {
        $editor = User::factory()->editor()->create();
        Milestone::factory()->create(['year' => 2024, 'title' => ['en' => 'First product launch']]);

        $this->actingAs($editor)->post(route('admin.milestones.store'), [
            'year' => '2019',
            'title' => ['ar' => '', 'en' => 'Company founded', 'tr' => ''],
            'description' => ['ar' => '', 'en' => '', 'tr' => ''],
            'is_visible' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAs($editor)->get(route('admin.milestones.index'))
            ->assertOk()
            ->assertSeeInOrder(['2019 · Company founded', '2024 · First product launch'])
            ->assertDontSee(__('admin.content.reorder_hint'));

        $this->assertFalse(app('router')->has('admin.milestones.move'));
    }

    public function test_the_year_must_be_plausible(): void
    {
        $this->actingAs(User::factory()->editor()->create())->post(route('admin.milestones.store'), [
            'year' => '3020',
            'title' => ['ar' => '', 'en' => 'Time travel', 'tr' => ''],
            'is_visible' => '1',
        ])->assertSessionHasErrors('year');
    }
}
