<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(User $user, array $overrides = []): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'locale' => '',
            'is_active' => $user->is_active ? '1' : '0',
            ...$overrides,
        ];
    }

    public function test_an_administrator_invites_a_new_user_who_chooses_their_own_password(): void
    {
        Notification::fake();

        $response = $this->actingAs(User::factory()->administrator()->create())->post(route('admin.users.store'), [
            'name' => 'Mira Kaya',
            'email' => ' Mira@Wasmsoft.Example ',
            'role' => Role::Editor->value,
            'locale' => 'tr',
        ]);

        $user = User::query()->where('email', 'mira@wasmsoft.example')->sole();
        $response->assertRedirect(route('admin.users.edit', $user));
        $this->assertSame(Role::Editor, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('tr', $user->locale);

        Notification::assertSentTo($user, ResetPasswordNotification::class, fn (ResetPasswordNotification $notification): bool => $notification->invitation);
        $this->assertDatabaseHas('user_invitation_tokens', ['email' => 'mira@wasmsoft.example']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'mira@wasmsoft.example']);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::UserCreated->value, 'subject_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::InvitationSent->value, 'subject_id' => $user->id]);
    }

    public function test_only_administrators_manage_users(): void
    {
        $editor = User::factory()->editor()->create();
        $other = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.users.store'), ['name' => 'X', 'email' => 'x@example.com', 'role' => 'administrator'])->assertForbidden();
        $this->actingAs($editor)->put(route('admin.users.update', $other), $this->payload($other, ['role' => 'administrator']))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.users.update', $editor), $this->payload($editor, ['role' => 'administrator']))->assertForbidden();

        $this->assertSame(Role::Editor, $editor->fresh()?->role);
    }

    public function test_a_role_change_and_a_deactivation_are_audited_and_sign_the_person_out(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->editor()->create();
        DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->getTimestamp()]);
        $rememberToken = $user->remember_token;

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('admin.users.update', $user), $this->payload($user, ['role' => Role::Author->value, 'is_active' => '0']))
            ->assertRedirect(route('admin.users.edit', $user));

        $user->refresh();
        $this->assertSame(Role::Author, $user->role);
        $this->assertFalse($user->is_active);
        $this->assertNotSame($rememberToken, $user->remember_token);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());

        $roleChange = AuditLog::query()->where('event', AuditEvent::UserRoleChanged)->sole();
        $this->assertSame(['from' => 'editor', 'to' => 'author'], $roleChange->properties);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::UserDeactivated->value, 'subject_id' => $user->id]);
    }

    public function test_the_last_active_administrator_keeps_their_role(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->administrator()->inactive()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->payload($admin, ['role' => Role::Editor->value]))
            ->assertSessionHasErrors('role');

        $this->assertSame(Role::Administrator, $admin->fresh()?->role);
    }

    public function test_an_administrator_may_step_down_while_another_one_remains(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->administrator()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->payload($admin, ['role' => Role::Editor->value]))
            ->assertSessionHasNoErrors();

        $this->assertSame(Role::Editor, $admin->fresh()?->role);
    }

    public function test_nobody_deactivates_or_deletes_their_own_account(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->administrator()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->payload($admin, ['is_active' => '0']))
            ->assertSessionHasErrors('is_active');
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();

        $this->assertTrue($admin->fresh()?->is_active);
    }

    public function test_email_addresses_are_unique_whatever_their_case(): void
    {
        User::factory()->create(['email' => 'team@wasmsoft.example']);

        $this->actingAs(User::factory()->administrator()->create())->post(route('admin.users.store'), [
            'name' => 'Duplicate',
            'email' => 'TEAM@wasmsoft.example',
            'role' => Role::Viewer->value,
        ])->assertSessionHasErrors('email');
    }

    public function test_deleting_a_user_keeps_their_articles(): void
    {
        $author = User::factory()->author()->create();
        $post = Post::factory()->by($author)->translated(['en'])->create();

        $this->actingAs(User::factory()->administrator()->create())->delete(route('admin.users.destroy', $author))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($author);
        $this->assertNull($post->fresh()?->author_id);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::UserDeleted->value, 'subject_id' => $author->id]);
    }

    public function test_an_invitation_can_be_sent_again_but_not_twice_a_minute(): void
    {
        Notification::fake();
        $admin = User::factory()->administrator()->create();
        $invited = User::factory()->unverified()->create();

        $this->actingAs($admin)->post(route('admin.users.invite', $invited))->assertSessionHas('status');
        $this->actingAs($admin)->post(route('admin.users.invite', $invited))->assertSessionHas('warning');

        $this->travel(2)->minutes();
        $this->actingAs($admin)->post(route('admin.users.invite', $invited))->assertSessionHas('status');

        Notification::assertSentToTimes($invited, ResetPasswordNotification::class, 2);
    }

    public function test_accounts_that_are_set_up_need_no_invitation(): void
    {
        Notification::fake();

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('admin.users.invite', User::factory()->create()))
            ->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_the_list_filters_by_role_and_status(): void
    {
        $admin = User::factory()->administrator()->create(['name' => 'Ada Admin']);
        User::factory()->editor()->create(['name' => 'Eda Editor']);
        User::factory()->author()->unverified()->create(['name' => 'Ivo Invited']);

        $editors = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'editor']));
        $editors->assertOk();
        $this->assertSame(['Eda Editor'], array_column($editors->inertiaProps('users'), 'name'));

        $invited = $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'invited']));
        $this->assertSame(['Ivo Invited'], array_column($invited->inertiaProps('users'), 'name'));
        $invited->assertInertia(fn (AssertableInertia $page) => $page->where('users.0.status.label', __('admin.users.status.invited')));
    }
}
