<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Role;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function audit(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    /**
     * An event as shown in the table (the filter's <option>s carry the same labels).
     */
    private function row(AuditEvent $event): string
    {
        return '<span class="font-semibold">'.e($event->label()).'</span>';
    }

    public function test_administrators_read_the_log_newest_first_with_readable_details(): void
    {
        $admin = User::factory()->administrator()->create(['name' => 'Ada Admin']);
        $editor = User::factory()->editor()->create(['name' => 'Eda Editor']);

        $this->travelTo(Carbon::parse('2026-10-20 09:00'));
        $this->audit()->record(AuditEvent::UserRoleChanged, $editor, ['from' => 'editor', 'to' => 'author'], $admin);
        $this->travelTo(Carbon::parse('2026-10-21 09:00'));
        $this->audit()->record(AuditEvent::SettingsUpdated, properties: ['changed' => ['contact.email', 'site.name']], actor: $admin);

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSeeInOrder([$this->row(AuditEvent::SettingsUpdated), $this->row(AuditEvent::UserRoleChanged)], false)
            ->assertSee(__('admin.audit.details.changed', ['fields' => 'contact.email, site.name']))
            ->assertSee('editor → author')
            ->assertSee(__('admin.audit.security'));
    }

    /**
     * @return array<string, array{Role}>
     */
    public static function rolesWithoutAccess(): array
    {
        return ['editor' => [Role::Editor], 'author' => [Role::Author], 'viewer' => [Role::Viewer]];
    }

    #[DataProvider('rolesWithoutAccess')]
    public function test_only_administrators_open_the_log(Role $role): void
    {
        $this->actingAs(User::factory()->role($role)->create())->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_entries_can_be_filtered_by_event_person_and_date(): void
    {
        $admin = User::factory()->administrator()->create();
        $editor = User::factory()->editor()->create();

        $this->travelTo(Carbon::parse('2026-10-01 12:00'));
        $this->audit()->record(AuditEvent::Login, $editor, actor: $editor);
        $this->travelTo(Carbon::parse('2026-10-15 12:00'));
        $this->audit()->record(AuditEvent::SettingsUpdated, properties: ['changed' => ['footer.note']], actor: $admin);

        $this->actingAs($admin)->get(route('admin.audit.index', ['event' => AuditEvent::Login->value]))
            ->assertSee($this->row(AuditEvent::Login), false)
            ->assertDontSee($this->row(AuditEvent::SettingsUpdated), false);

        $this->actingAs($admin)->get(route('admin.audit.index', ['user' => $admin->id]))
            ->assertSee($this->row(AuditEvent::SettingsUpdated), false)
            ->assertDontSee($this->row(AuditEvent::Login), false);

        $this->actingAs($admin)->get(route('admin.audit.index', ['from' => '2026-10-10', 'until' => '2026-10-31']))
            ->assertSee($this->row(AuditEvent::SettingsUpdated), false)
            ->assertDontSee($this->row(AuditEvent::Login), false);
    }

    public function test_attempted_sign_in_addresses_are_shown_escaped(): void
    {
        $this->audit()->record(AuditEvent::LoginFailed, properties: ['email' => '<script>alert(1)</script>']);

        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.audit.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee(__('admin.audit.anonymous'));
    }

    public function test_items_link_to_their_page_while_they_exist(): void
    {
        $admin = User::factory()->administrator()->create();
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Quarterly update']])->create();
        $this->audit()->record(AuditEvent::ContentUpdated, $post, actor: $admin);

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertSee('href="'.route('admin.posts.edit', $post).'"', false);

        $post->delete();

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertSee('Quarterly update')
            ->assertDontSee('href="'.route('admin.posts.edit', $post).'"', false);
    }
}
