<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Content\PublicationStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostControllerTest extends TestCase
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
            'published_at' => '',
            'author_id' => '',
            'post_category_id' => '',
            'cover_id' => '',
            'is_featured' => '0',
            'translations' => [
                'ar' => [...$empty, ...($translations['ar'] ?? [])],
                'en' => [...$empty, ...($translations['en'] ?? [])],
                'tr' => [...$empty, ...($translations['tr'] ?? [])],
            ],
            ...$overrides,
        ];
    }

    /**
     * The rendered checkbox of a tag in the post form.
     */
    private function tagCheckbox(string $html, Tag $tag): string
    {
        preg_match('/<input[^>]*name="tags\[\]"[^>]*value="'.$tag->id.'"[^>]*>/s', $html, $match);

        return $match[0] ?? '';
    }

    public function test_editor_publishes_an_article_with_category_tags_cover_and_author(): void
    {
        $editor = User::factory()->editor()->create();
        $writer = User::factory()->author()->create();
        $category = PostCategory::factory()->translated(['en'])->create();
        [$laravel, $accessibility] = Tag::factory()->count(2)->translated(['en'])->create();
        $cover = Media::factory()->create();

        $response = $this->actingAs($editor)->post(route('admin.posts.store'), $this->payload(
            ['en' => ['title' => 'We joined the accessibility pledge', 'summary' => 'Why it matters.', 'body' => 'Every product we ship is tested.']],
            [
                'status' => PublicationStatus::Published->value,
                'author_id' => (string) $writer->id,
                'post_category_id' => (string) $category->id,
                'cover_id' => (string) $cover->id,
                'tags' => [(string) $laravel->id, (string) $accessibility->id],
                'is_featured' => '1',
            ],
        ));

        $post = Post::query()->with(['tags', 'translations'])->sole();
        $response->assertRedirect(route('admin.posts.edit', $post));
        $this->assertTrue($post->isPublished());
        $this->assertSame($writer->id, $post->author_id);
        $this->assertSame($category->id, $post->post_category_id);
        $this->assertSame($cover->id, $post->cover_id);
        $this->assertEqualsCanonicalizing([$laravel->id, $accessibility->id], $post->tags->modelKeys());
        $this->assertTrue($post->is_featured);
        $this->assertSame('we-joined-the-accessibility-pledge', $post->translation('en')?->slug);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentCreated->value, 'subject_type' => 'post']);
    }

    public function test_editor_schedules_an_article_in_the_display_time_zone(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 12:00:00', 'UTC'));
        config(['wasmsoft.display_timezone' => 'Europe/Istanbul']);

        $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(
            ['en' => ['title' => 'Launch day']],
            ['status' => PublicationStatus::Published->value, 'published_at' => '2026-11-02T09:30'],
        ));

        $post = Post::query()->sole();
        $this->assertSame('2026-11-02 06:30:00', $post->published_at?->utc()->toDateTimeString());
        $this->assertTrue($post->isScheduled());
        $this->assertFalse($post->isPublished());
    }

    public function test_saving_an_unchanged_publication_date_keeps_it_exactly(): void
    {
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Release notes']])->create([
            'status' => PublicationStatus::Published,
            'published_at' => Carbon::parse('2026-10-20 08:15:42', 'UTC'),
        ]);

        $this->actingAs(User::factory()->editor()->create())->put(route('admin.posts.update', $post), $this->payload(
            ['en' => ['title' => 'Release notes, updated']],
            ['status' => PublicationStatus::Published->value, 'published_at' => '2026-10-20T08:15'],
        ));

        $this->assertSame('2026-10-20 08:15:42', $post->fresh()?->published_at?->toDateTimeString());
    }

    public function test_editor_may_publish_without_naming_an_author(): void
    {
        $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(['en' => ['title' => 'Office move']]));

        $this->assertNull(Post::query()->sole()->author_id);
    }

    public function test_the_author_must_be_someone_who_writes_articles(): void
    {
        $viewer = User::factory()->role(Role::Viewer)->create();
        $deactivated = User::factory()->author()->inactive()->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(['en' => ['title' => 'A']], ['author_id' => (string) $viewer->id]));
        $response->assertSessionHasErrors('author_id');

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(['en' => ['title' => 'B']], ['author_id' => (string) $deactivated->id]));
        $response->assertSessionHasErrors('author_id');
    }

    public function test_a_deactivated_author_stays_on_their_existing_articles(): void
    {
        $former = User::factory()->author()->inactive()->create();
        $post = Post::factory()->by($former)->translated(['en'], ['en' => ['title' => 'Old article']])->create();

        $response = $this->actingAs(User::factory()->editor()->create())->put(route('admin.posts.update', $post), $this->payload(
            ['en' => ['title' => 'Old article']],
            ['author_id' => (string) $former->id],
        ));

        $response->assertSessionHasNoErrors();
        $this->assertSame($former->id, $post->fresh()?->author_id);
    }

    public function test_author_writes_a_draft_under_their_own_name_whatever_else_is_submitted(): void
    {
        $author = User::factory()->author()->create();
        $someoneElse = User::factory()->editor()->create();

        $this->actingAs($author)->post(route('admin.posts.store'), $this->payload(
            ['en' => ['title' => 'My first article']],
            ['published_at' => '2026-01-01T10:00', 'is_featured' => '1', 'author_id' => (string) $someoneElse->id],
        ))->assertSessionHasNoErrors();

        $post = Post::query()->sole();
        $this->assertSame(PublicationStatus::Draft, $post->status);
        $this->assertSame($author->id, $post->author_id);
        $this->assertNull($post->published_at);
        $this->assertFalse($post->is_featured);
    }

    public function test_authors_cannot_publish(): void
    {
        $response = $this->actingAs(User::factory()->author()->create())->post(route('admin.posts.store'), $this->payload(
            ['en' => ['title' => 'Straight to the website']],
            ['status' => PublicationStatus::Published->value],
        ));

        $response->assertSessionHasErrors('status');
        $this->assertSame(0, Post::query()->count());
    }

    public function test_authors_edit_their_own_drafts_only(): void
    {
        $author = User::factory()->author()->create();
        $own = Post::factory()->by($author)->translated(['en'], ['en' => ['title' => 'Mine']])->create();
        $colleagues = Post::factory()->by(User::factory()->author()->create())->translated(['en'], ['en' => ['title' => 'Theirs']])->create();

        $this->actingAs($author)->put(route('admin.posts.update', $own), $this->payload(['en' => ['title' => 'Mine, improved']]))
            ->assertRedirect(route('admin.posts.edit', $own));
        $this->actingAs($author)->put(route('admin.posts.update', $colleagues), $this->payload(['en' => ['title' => 'Hijacked']]))
            ->assertForbidden();
        $this->actingAs($author)->delete(route('admin.posts.destroy', $colleagues))->assertForbidden();

        $this->assertSame('Mine, improved', $own->fresh()?->translations()->value('title'));
        $this->assertModelExists($colleagues);
    }

    public function test_published_articles_are_locked_for_their_author(): void
    {
        $author = User::factory()->author()->create();
        $post = Post::factory()->published()->by($author)->translated(['en'], ['en' => ['title' => 'Live article']])->create();

        $this->actingAs($author)->put(route('admin.posts.update', $post), $this->payload(['en' => ['title' => 'Changed']]))->assertForbidden();
        $this->actingAs($author)->delete(route('admin.posts.destroy', $post))->assertForbidden();

        $this->actingAs($author)->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee(__('admin.posts.locked_own'));
    }

    public function test_authors_delete_their_own_drafts(): void
    {
        $author = User::factory()->author()->create();
        $post = Post::factory()->by($author)->translated(['en'])->create();

        $this->actingAs($author)->delete(route('admin.posts.destroy', $post))->assertRedirect(route('admin.posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_viewers_cannot_write_articles(): void
    {
        $viewer = User::factory()->role(Role::Viewer)->create();

        $this->actingAs($viewer)->get(route('admin.posts.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('admin.posts.store'), $this->payload(['en' => ['title' => 'A']]))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.posts.index'))->assertOk();
    }

    public function test_slugs_used_by_the_news_routes_are_rejected(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(['en' => ['title' => 'Categories', 'slug' => 'category']]));

        $response->assertSessionHasErrors('translations.en.slug');
    }

    public function test_an_article_has_at_most_ten_tags(): void
    {
        $tags = Tag::factory()->count(11)->translated(['en'])->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.posts.store'), $this->payload(
            ['en' => ['title' => 'Tagged']],
            ['tags' => array_map(strval(...), $tags->modelKeys())],
        ));

        $response->assertSessionHasErrors('tags');
    }

    public function test_unchecked_tags_stay_unchecked_after_a_failed_submission(): void
    {
        $editor = User::factory()->editor()->create();
        $tag = Tag::factory()->translated(['en'])->create();
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Tagged']])->create();
        $post->tags()->attach($tag);

        $this->assertStringContainsString('checked', $this->tagCheckbox($this->actingAs($editor)->get(route('admin.posts.edit', $post))->getContent() ?: '', $tag));

        // All tags unchecked, but the title emptied: the form comes back with errors.
        $this->actingAs($editor)
            ->from(route('admin.posts.edit', $post))
            ->put(route('admin.posts.update', $post), $this->payload())
            ->assertSessionHasErrors();

        $html = $this->actingAs($editor)->get(route('admin.posts.edit', $post))->getContent() ?: '';

        $this->assertNotSame('', $this->tagCheckbox($html, $tag));
        $this->assertStringNotContainsString('checked', $this->tagCheckbox($html, $tag));
    }

    public function test_index_filters_by_status_including_scheduled_and_by_author(): void
    {
        $author = User::factory()->author()->create(['name' => 'Rana Writer']);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Already live']])->create();
        Post::factory()->scheduled()->translated(['en'], ['en' => ['title' => 'Coming next week']])->create();
        Post::factory()->by($author)->translated(['en'], ['en' => ['title' => 'Draft by Rana']])->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('admin.posts.index', ['status' => 'scheduled']))
            ->assertOk()
            ->assertSee('Coming next week')
            ->assertDontSee('Already live')
            ->assertSee(__('admin.content.scheduled'));

        $this->actingAs($editor)->get(route('admin.posts.index', ['status' => 'published']))
            ->assertSee('Already live')
            ->assertDontSee('Coming next week');

        $this->actingAs($editor)->get(route('admin.posts.index', ['author' => $author->id]))
            ->assertSee('Draft by Rana')
            ->assertDontSee('Already live');
    }

    public function test_cover_images_cannot_be_deleted_from_the_library(): void
    {
        $cover = Media::factory()->create();
        Post::factory()->translated(['en'])->create(['cover_id' => $cover->id]);

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.media.destroy', $cover));

        $this->assertModelExists($cover);
    }

    public function test_preview_shows_a_draft_that_is_never_indexed(): void
    {
        $post = Post::factory()->translated(['en'], ['en' => ['title' => 'Embargoed announcement']])->create();

        $response = $this->actingAs(User::factory()->author()->create())->get(route('admin.posts.preview', [$post, 'en']));

        $response->assertOk();
        $response->assertSee('Embargoed announcement');
        $response->assertSee(__('site.preview.banner'));
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
