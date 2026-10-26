<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PostCategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_creates_a_category(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.post-categories.store'), ['translations' => [
            'ar' => ['name' => 'أخبار الشركة', 'slug' => ''],
            'en' => ['name' => 'Company news', 'slug' => ''],
            'tr' => ['name' => '', 'slug' => ''],
        ]]);

        $category = PostCategory::query()->with('translations')->sole();
        $response->assertRedirect(route('admin.post-categories.edit', $category));
        $this->assertSame('company-news', $category->translation('en')?->slug);
        $this->assertSame('أخبار-الشركة', $category->translation('ar')?->slug);
    }

    public function test_index_counts_the_articles_of_each_category(): void
    {
        $category = PostCategory::factory()->translated(['en'], ['en' => ['name' => 'Insights']])->create();
        Post::factory()->count(3)->translated(['en'])->create(['post_category_id' => $category->id]);

        $this->actingAs(User::factory()->author()->create())->get(route('admin.post-categories.index'))
            ->assertOk()
            ->assertSee('Insights')
            ->assertSee(trans_choice('admin.post_categories.usage', 3, ['count' => 3]));
    }

    public function test_deleting_a_category_keeps_its_articles(): void
    {
        $category = PostCategory::factory()->translated(['en'])->create();
        $post = Post::factory()->translated(['en'])->create(['post_category_id' => $category->id]);

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.post-categories.destroy', $category));

        $this->assertModelMissing($category);
        $this->assertNull($post->fresh()?->post_category_id);
    }
}
