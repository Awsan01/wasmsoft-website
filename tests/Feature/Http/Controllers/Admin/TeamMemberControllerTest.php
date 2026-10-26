<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TeamMemberControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Omar Haddad',
            'role' => ['ar' => 'مهندس برمجيات', 'en' => 'Software engineer', 'tr' => ''],
            'bio' => ['ar' => '', 'en' => '', 'tr' => ''],
            'photo_id' => '',
            'is_visible' => '1',
            ...$overrides,
        ];
    }

    public function test_editor_adds_a_team_member(): void
    {
        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.team-members.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $member = TeamMember::query()->sole();
        $this->assertSame('Omar Haddad', $member->name);
        $this->assertSame(['ar' => 'مهندس برمجيات', 'en' => 'Software engineer'], $member->role);
        $this->assertNull($member->bio);
    }

    public function test_a_role_is_needed_in_at_least_one_language(): void
    {
        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.team-members.store'), $this->payload(['role' => ['ar' => '', 'en' => '', 'tr' => '']]))
            ->assertSessionHasErrors('role.en');
    }

    public function test_people_can_be_reordered(): void
    {
        [$first, $second] = TeamMember::factory()->count(2)->create();

        $this->actingAs(User::factory()->editor()->create())->patch(route('admin.team-members.move', $second), ['direction' => 'up']);

        $this->assertSame([$second->id, $first->id], TeamMember::query()->ordered()->pluck('id')->all());
    }
}
