<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Role;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function audit(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    /**
     * The events of the listed entries, in the order shown.
     *
     * @param  TestResponse<Response>  $response
     * @return list<string>
     */
    private function events(TestResponse $response): array
    {
        return array_column($response->inertiaProps('entries'), 'event');
    }

    public function test_administrators_read_the_log_newest_first_with_readable_details(): void
    {
        $admin = User::factory()->administrator()->create(['name' => 'Ada Admin']);
        $editor = User::factory()->editor()->create(['name' => 'Eda Editor']);

        $this->travelTo(Carbon::parse('2026-10-20 09:00'));
        $this->audit()->record(AuditEvent::UserRoleChanged, $editor, ['from' => 'editor', 'to' => 'author'], $admin);
        $this->travelTo(Carbon::parse('2026-10-21 09:00'));
        $this->audit()->record(AuditEvent::SettingsUpdated, properties: ['changed' => ['contact.email', 'site.name']], actor: $admin);

        $response = $this->actingAs($admin)->get(route('admin.audit.index'));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/audit/index')
            ->where('entries.0.event', AuditEvent::SettingsUpdated->label())
            ->where('entries.0.details', [__('admin.audit.details.changed', ['fields' => 'contact.email, site.name'])])
            ->where('entries.1.event', AuditEvent::UserRoleChanged->label())
            ->where('entries.1.security', true)
            ->where('entries.1.who', 'Ada Admin')
            ->where('entries.1.details.0', fn (string $line): bool => str_contains($line, 'editor → author'))
        );
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

        $byEvent = $this->actingAs($admin)->get(route('admin.audit.index', ['event' => AuditEvent::Login->value]));
        $this->assertSame([AuditEvent::Login->label()], $this->events($byEvent));
        $byEvent->assertInertia(fn (AssertableInertia $page) => $page->where('filters.event', AuditEvent::Login->value));

        $byPerson = $this->actingAs($admin)->get(route('admin.audit.index', ['user' => $admin->id]));
        $this->assertSame([AuditEvent::SettingsUpdated->label()], $this->events($byPerson));

        $byDate = $this->actingAs($admin)->get(route('admin.audit.index', ['from' => '2026-10-10', 'until' => '2026-10-31']));
        $this->assertSame([AuditEvent::SettingsUpdated->label()], $this->events($byDate));
        $byDate->assertInertia(fn (AssertableInertia $page) => $page->where('filters.from', '2026-10-10')->where('filters.until', '2026-10-31'));
    }

    public function test_attempted_sign_in_addresses_are_shown_escaped(): void
    {
        $this->audit()->record(AuditEvent::LoginFailed, properties: ['email' => '<script>alert(1)</script>']);

        $response = $this->actingAs(User::factory()->administrator()->create())->get(route('admin.audit.index'));

        // The page data is JSON-encoded in the document, and React shows it as text.
        $response->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertPageShows('<script>alert(1)</script>')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('entries.0.who', null));
    }

    public function test_every_event_and_item_type_has_a_name_in_every_language(): void
    {
        foreach (['ar', 'en', 'tr'] as $locale) {
            foreach (AuditEvent::cases() as $event) {
                $key = 'admin.audit.events.'.str_replace('.', '_', $event->value);
                $this->assertTrue(Lang::has($key, $locale, false), "Missing [{$locale}] {$key}");
            }

            foreach (array_keys(Relation::morphMap()) as $alias) {
                $this->assertTrue(Lang::has("admin.audit.subjects.{$alias}", $locale, false), "Missing [{$locale}] admin.audit.subjects.{$alias}");
            }
        }
    }

    public function test_items_link_to_their_page_while_they_exist(): void
    {
        $admin = User::factory()->administrator()->create();
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Quarterly update']])->create();
        $this->audit()->record(AuditEvent::ContentUpdated, $post, actor: $admin);

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('entries.0.subject.url', route('admin.posts.edit', $post)));

        $post->delete();

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('entries.0.subject.label', 'Quarterly update')
                ->where('entries.0.subject.url', null)
            );
    }
}
