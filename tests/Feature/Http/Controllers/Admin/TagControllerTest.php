<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Tags use the shared vocabulary management without manual ordering.
 */
class TagControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_creates_a_tag(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.tags.store'), ['translations' => [
            'ar' => ['name' => '', 'slug' => ''],
            'en' => ['name' => 'Cloud hosting', 'slug' => ''],
            'tr' => ['name' => '', 'slug' => ''],
        ]]);

        $tag = Tag::query()->with('translations')->sole();
        $response->assertRedirect(route('admin.tags.edit', $tag));
        $this->assertSame('cloud-hosting', $tag->translation('en')?->slug);
    }

    public function test_tags_are_listed_alphabetically_and_cannot_be_reordered(): void
    {
        Tag::factory()->translated(['en'], ['en' => ['name' => 'Security']])->create();
        $first = Tag::factory()->translated(['en'], ['en' => ['name' => 'accessibility']])->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('admin.tags.index'))
            ->assertOk()
            ->assertSeeInOrder(['accessibility', 'Security'])
            ->assertDontSee(__('admin.content.reorder_hint'));

        $this->assertFalse(app('router')->has('admin.tags.move'));
    }

    public function test_deleting_a_tag_keeps_its_articles(): void
    {
        $tag = Tag::factory()->translated(['en'])->create();
        $post = Post::factory()->translated(['en'])->create();
        $post->tags()->attach($tag);

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.tags.destroy', $tag))->assertRedirect(route('admin.tags.index'));

        $this->assertModelMissing($tag);
        $this->assertModelExists($post);
        $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    }

    public function test_authors_use_tags_but_cannot_manage_them(): void
    {
        $author = User::factory()->author()->create();
        $tag = Tag::factory()->translated(['en'])->create();

        $this->actingAs($author)->get(route('admin.tags.index'))->assertOk();
        $this->actingAs($author)->get(route('admin.tags.create'))->assertForbidden();
        $this->actingAs($author)->delete(route('admin.tags.destroy', $tag))->assertForbidden();
    }
}
