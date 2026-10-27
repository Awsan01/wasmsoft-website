<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Auth\Role;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{Role}>
     */
    public static function roles(): array
    {
        return collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => [$role]])->all();
    }

    /**
     * A headline figure of the overview, by key ("posts", "inquiries").
     *
     * @param  TestResponse<Response>  $response
     * @return array<string, mixed>|null
     */
    private function figure(TestResponse $response, string $key): ?array
    {
        return collect($response->inertiaProps('figures'))->firstWhere('key', $key);
    }

    /**
     * The labels of the sidebar links.
     *
     * @param  TestResponse<Response>  $response
     * @return list<string>
     */
    private function sidebar(TestResponse $response): array
    {
        return collect($response->inertiaProps('navigation'))->flatMap(fn (array $group): array => array_column($group['links'], 'label'))->values()->all();
    }

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    #[DataProvider('roles')]
    public function test_every_role_can_open_the_overview(Role $role): void
    {
        $user = User::factory()->role($role)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('admin/dashboard')->where('role.label', $role->label()));
        $response->assertHeader('Cache-Control', 'no-store, private');
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_deactivated_user_is_signed_out_with_an_explanation(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('admin.auth.deactivated')]);
        $this->assertGuest();
    }

    public function test_sidebar_only_lists_modules_the_user_may_open(): void
    {
        $user = User::factory()->role(Role::Viewer)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $this->assertContains(__('admin.nav.overview'), $this->sidebar($response));
        $this->assertNotContains(__('admin.nav.users'), $this->sidebar($response));
        $this->assertNotContains(__('admin.nav.settings'), $this->sidebar($response));
    }

    public function test_figures_count_what_is_actually_published(): void
    {
        Post::factory()->count(2)->published()->translated(['en'])->create();
        Post::factory()->translated(['en'])->create();
        Post::factory()->scheduled()->translated(['en'])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $posts = $this->figure($response, 'posts');
        $this->assertSame(__('admin.overview.stats.posts'), $posts['label'] ?? null);
        $this->assertSame('2', $posts['value'] ?? null);
        $this->assertSame(trans_choice('admin.overview.drafts', 1, ['count' => 1]).' · '.trans_choice('admin.overview.scheduled', 1, ['count' => 1]), $posts['detail'] ?? null);
    }

    public function test_new_inquiries_are_counted_for_those_who_handle_them(): void
    {
        Inquiry::factory()->count(3)->create();

        $editor = $this->actingAs(User::factory()->editor()->create())->get(route('admin.dashboard'));
        $this->assertSame('3', $this->figure($editor, 'inquiries')['value'] ?? null);

        $viewer = $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'));
        $this->assertNull($this->figure($viewer, 'inquiries'));
    }

    public function test_published_items_without_every_language_are_listed(): void
    {
        Project::factory()->published()->translated(['en'])->create();

        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('missing.rows', 1)
                ->where('missing.rows.0.label', __('admin.nav.projects'))
                ->where('missing.rows.0.counts', [
                    ['code' => 'ar', 'count' => 1, 'label' => '1'],
                    ['code' => 'en', 'count' => 0, 'label' => '0'],
                    ['code' => 'tr', 'count' => 1, 'label' => '1'],
                ])
            );
    }

    public function test_system_pages_are_not_missing_translations(): void
    {
        // The system pages exist without translations: they fall back to their built-in text.
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.pages.index'));

        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('missing', null));
    }

    public function test_editors_see_the_drafts_authors_wrote_and_authors_see_their_own(): void
    {
        $author = User::factory()->author()->create();
        Post::factory()->by($author)->translated(['en'], ['en' => ['title' => 'Draft from the author']])->create();

        $this->actingAs(User::factory()->editor()->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('review.heading', trans_choice('admin.overview.awaiting_review', 1, ['count' => 1]))
                ->where('review.posts.0.title.text', 'Draft from the author')
                ->where('ownDrafts', null)
            );

        $this->actingAs($author)->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('review', null)
                ->where('ownDrafts.0.title.text', 'Draft from the author')
            );

        // Viewers read every article (the recent list shows it) but review nothing.
        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('review', null)
                ->where('ownDrafts', null)
                ->where('recent.posts.0.title.text', 'Draft from the author')
            );
    }

    public function test_recent_activity_shows_content_changes_but_no_security_events(): void
    {
        $editor = User::factory()->editor()->create(['name' => 'Eda Editor']);
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Launch news']])->create();
        app(AuditLogger::class)->record(AuditEvent::ContentPublished, $post, actor: $editor);
        app(AuditLogger::class)->record(AuditEvent::Login, $editor, actor: $editor);

        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('activity', 1)
                ->where('activity.0.event', AuditEvent::ContentPublished->label())
                ->where('activity.0.subject', 'Launch news')
                ->where('activity.0.who', 'Eda Editor')
            );
    }

    public function test_shortcuts_follow_the_permissions(): void
    {
        $author = $this->actingAs(User::factory()->author()->create())->get(route('admin.dashboard'));
        $labels = array_column($author->inertiaProps('shortcuts'), 'label');
        $this->assertContains(__('admin.posts.new'), $labels);
        $this->assertNotContains(__('admin.projects.new'), $labels);

        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('shortcuts', []));
    }

    public function test_user_name_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        // The page data is JSON-encoded in the document (no markup), and React shows it as text.
        $response->assertOk();
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('heading', __('admin.overview.welcome', ['name' => '<img src=x onerror=alert(1)>']))
            ->where('auth.user.name', '<img src=x onerror=alert(1)>')
        );
    }
}
