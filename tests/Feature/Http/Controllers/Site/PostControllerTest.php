<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_published_articles_of_this_language_newest_first(): void
    {
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Older article']])->create(['published_at' => now()->subWeek()]);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Newer article']])->create(['published_at' => now()->subDay()]);
        Post::factory()->translated(['en'], ['en' => ['title' => 'Draft article']])->create();
        Post::factory()->scheduled()->translated(['en'], ['en' => ['title' => 'Scheduled article']])->create();
        Post::factory()->published()->translated(['tr'], ['tr' => ['title' => 'Yalnızca Türkçe yazı']])->create();

        $response = $this->get('/en/news');

        $response->assertOk();
        $response->assertSeeInOrder(['Newer article', 'Older article']);
        $response->assertDontSee('Draft article');
        $response->assertDontSee('Scheduled article');
        $response->assertDontSee('Yalnızca Türkçe yazı');
    }

    public function test_later_pages_have_their_own_canonical_url(): void
    {
        config(['wasmsoft.pagination.site' => 1]);
        Post::factory()->count(2)->published()->translated(['en'])->create();

        $response = $this->get('/en/news?page=2');

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.url('/en/news?page=2').'">', false);
    }

    public function test_a_scheduled_article_appears_once_its_time_has_come(): void
    {
        Post::factory()->translated(['en'], ['en' => ['slug' => 'launch']])->create(['status' => 'published', 'published_at' => now()->addHour()]);

        $this->get('/en/news/launch')->assertNotFound();

        $this->travel(2)->hours();

        $this->get('/en/news/launch')->assertOk();
    }

    public function test_article_shows_author_date_reading_time_category_and_tags(): void
    {
        $category = PostCategory::factory()->translated(['en'], ['en' => ['name' => 'Company news', 'slug' => 'company-news']])->create();
        $tag = Tag::factory()->translated(['en'], ['en' => ['name' => 'Accessibility', 'slug' => 'accessibility']])->create();
        $post = Post::factory()->published()->by(User::factory()->author()->create(['name' => 'Lina Haddad']))->translated(['en'], ['en' => [
            'title' => 'Accessible by default',
            'slug' => 'accessible-by-default',
            'body' => str_repeat('word ', 450),
        ]])->create(['post_category_id' => $category->id]);
        $post->tags()->attach($tag);

        $response = $this->get('/en/news/accessible-by-default');

        $response->assertOk();
        $response->assertSee('Lina Haddad');
        $response->assertSee('3 min read');
        $response->assertSee('href="'.url('/en/news/category/company-news').'"', false);
        $response->assertSee('href="'.url('/en/news/tag/accessibility').'"', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="article:published_time" content="'.$post->published_at?->toAtomString().'">', false);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('"author":{"@type":"Person","name":"Lina Haddad"}', false);
    }

    public function test_without_a_named_author_the_company_is_the_author(): void
    {
        Post::factory()->published()->translated(['en'], ['en' => ['slug' => 'team-update']])->create();

        $response = $this->get('/en/news/team-update');

        $response->assertOk();
        $response->assertSee('"author":{"@type":"Organization"', false);
    }

    public function test_editor_entered_text_is_escaped(): void
    {
        Post::factory()->published()->translated(['en'], ['en' => ['slug' => 'escaped', 'title' => '<script>alert(1)</script>', 'body' => '<img src=x onerror=alert(1)>']])->create();

        $response = $this->get('/en/news/escaped');

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_category_page_lists_its_articles_and_redirects_slugs_of_other_languages(): void
    {
        $category = PostCategory::factory()->translated(['en', 'ar'], [
            'en' => ['name' => 'Insights', 'slug' => 'insights'],
            'ar' => ['name' => 'رؤى', 'slug' => 'رؤى'],
        ])->create();
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'In the category']])->create(['post_category_id' => $category->id]);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Elsewhere']])->create();

        $response = $this->get('/en/news/category/insights');

        $response->assertOk();
        $response->assertSee('In the category');
        $response->assertDontSee('Elsewhere');
        $response->assertSee('<link rel="alternate" hreflang="ar" href="'.url('/ar/news/category/'.rawurlencode('رؤى')).'">', false);

        $this->get('/ar/news/category/insights')->assertRedirect('/ar/news/category/'.rawurlencode('رؤى'));
    }

    public function test_an_empty_category_page_is_not_indexed(): void
    {
        PostCategory::factory()->translated(['en'], ['en' => ['slug' => 'empty']])->create();

        $response = $this->get('/en/news/category/empty');

        $response->assertOk();
        $response->assertSee(__('site.news.empty'));
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_tag_page_lists_the_articles_with_that_tag(): void
    {
        $tag = Tag::factory()->translated(['en'], ['en' => ['name' => 'Laravel', 'slug' => 'laravel']])->create();
        $tagged = Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Upgrading to Laravel 13']])->create();
        $tagged->tags()->attach($tag);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Untagged article']])->create();

        $response = $this->get('/en/news/tag/laravel');

        $response->assertOk();
        $response->assertSee('Upgrading to Laravel 13');
        $response->assertDontSee('Untagged article');
    }

    public function test_unknown_category_and_tag_are_not_found(): void
    {
        $this->get('/en/news/category/nothing')->assertNotFound();
        $this->get('/en/news/tag/nothing')->assertNotFound();
    }

    public function test_related_articles_come_from_the_same_category(): void
    {
        $category = PostCategory::factory()->translated(['en'])->create();
        Post::factory()->published()->translated(['en'], ['en' => ['slug' => 'main']])->create(['post_category_id' => $category->id]);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Same category article']])->create(['post_category_id' => $category->id]);
        Post::factory()->published()->translated(['en'], ['en' => ['title' => 'Other article']])->create();

        $response = $this->get('/en/news/main');

        $response->assertSee('Same category article');
        $response->assertDontSee('Other article');
    }

    public function test_article_only_in_another_language_lists_its_languages(): void
    {
        Post::factory()->published()->translated(['ar'], ['ar' => ['slug' => 'خبر']])->create();

        $response = $this->get('/en/news/'.rawurlencode('خبر'));

        $response->assertNotFound();
        $response->assertSee(url('/ar/news/'.rawurlencode('خبر')), false);
    }
}
