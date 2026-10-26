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
use PHPUnit\Framework\Attributes\DataProvider;
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
        $response->assertSee($role->label());
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

        $response->assertSee(__('admin.nav.overview'));
        $response->assertDontSee(__('admin.nav.users'));
        $response->assertDontSee(__('admin.nav.settings'));
    }

    public function test_figures_count_what_is_actually_published(): void
    {
        Post::factory()->count(2)->published()->translated(['en'])->create();
        Post::factory()->translated(['en'])->create();
        Post::factory()->scheduled()->translated(['en'])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertSeeInOrder([__('admin.overview.stats.posts'), '2', trans_choice('admin.overview.drafts', 1, ['count' => 1]).' · '.__('admin.overview.scheduled', ['count' => 1])]);
    }

    public function test_new_inquiries_are_counted_for_those_who_handle_them(): void
    {
        Inquiry::factory()->count(3)->create();

        $this->actingAs(User::factory()->editor()->create())->get(route('admin.dashboard'))
            ->assertSeeInOrder([__('admin.overview.stats.inquiries'), '3']);
        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertDontSee(__('admin.overview.stats.inquiries'));
    }

    public function test_published_items_without_every_language_are_listed(): void
    {
        Project::factory()->published()->translated(['en'])->create();

        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))
            ->assertSee(__('admin.overview.missing_title'))
            ->assertSeeInOrder([__('admin.nav.projects').'</a>', '1', '0', '1'], false);
    }

    public function test_system_pages_are_not_missing_translations(): void
    {
        // The system pages exist without translations: they fall back to their built-in text.
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.pages.index'));

        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))
            ->assertDontSee(__('admin.overview.missing_title'))
            ->assertSee(__('admin.overview.nothing_needed'));
    }

    public function test_editors_see_the_drafts_authors_wrote_and_authors_see_their_own(): void
    {
        $author = User::factory()->author()->create();
        Post::factory()->by($author)->translated(['en'], ['en' => ['title' => 'Draft from the author']])->create();

        $this->actingAs(User::factory()->editor()->create())->get(route('admin.dashboard'))
            ->assertSee(trans_choice('admin.overview.awaiting_review', 1, ['count' => 1]))
            ->assertSee('Draft from the author');

        $this->actingAs($author)->get(route('admin.dashboard'))
            ->assertSee(__('admin.overview.own_drafts'))
            ->assertSee('Draft from the author');

        // Viewers read every article (the recent list shows it) but review nothing.
        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertDontSee(trans_choice('admin.overview.awaiting_review', 1, ['count' => 1]))
            ->assertDontSee(__('admin.overview.own_drafts'));
    }

    public function test_recent_activity_shows_content_changes_but_no_security_events(): void
    {
        $editor = User::factory()->editor()->create(['name' => 'Eda Editor']);
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Launch news']])->create();
        app(AuditLogger::class)->record(AuditEvent::ContentPublished, $post, actor: $editor);
        app(AuditLogger::class)->record(AuditEvent::Login, $editor, actor: $editor);

        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertSee(AuditEvent::ContentPublished->label())
            ->assertSee('Launch news')
            ->assertDontSee(AuditEvent::Login->label());
    }

    public function test_shortcuts_follow_the_permissions(): void
    {
        $this->actingAs(User::factory()->author()->create())->get(route('admin.dashboard'))
            ->assertSee(__('admin.posts.new'))
            ->assertDontSee(__('admin.projects.new'));

        $this->actingAs(User::factory()->role(Role::Viewer)->create())->get(route('admin.dashboard'))
            ->assertDontSee(__('admin.overview.shortcuts'));
    }

    public function test_user_name_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
