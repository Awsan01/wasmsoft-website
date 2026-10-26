<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Tag;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_fixed_pages_are_listed_in_every_language_with_their_alternates(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<loc>'.url('/ar/contact').'</loc>', false);
        $response->assertSee('<loc>'.url('/tr/news').'</loc>', false);
        $response->assertSee('<xhtml:link rel="alternate" hreflang="en" href="'.url('/en/projects').'"/>', false);
    }

    public function test_only_published_content_is_listed_in_the_languages_it_exists_in(): void
    {
        $project = Project::factory()->published()->translated(['en', 'ar'], [
            'en' => ['slug' => 'fleet-portal'],
            'ar' => ['slug' => 'بوابة'],
        ])->create();
        Project::factory()->translated(['en'], ['en' => ['slug' => 'draft-project']])->create();
        Post::factory()->scheduled()->translated(['en'], ['en' => ['slug' => 'coming-soon']])->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/en/projects/fleet-portal').'</loc>', false);
        $response->assertSee('<loc>'.url('/ar/projects/'.rawurlencode('بوابة')).'</loc>', false);
        $response->assertDontSee('/tr/projects/fleet-portal', false);
        $response->assertSee('<lastmod>'.$project->updated_at->toAtomString().'</lastmod>', false);
        $response->assertDontSee('draft-project', false);
        $response->assertDontSee('coming-soon', false);
    }

    public function test_categories_and_tags_are_listed_where_they_have_published_articles(): void
    {
        $used = PostCategory::factory()->translated(['en'], ['en' => ['slug' => 'insights']])->create();
        PostCategory::factory()->translated(['en'], ['en' => ['slug' => 'empty-category']])->create();
        $tag = Tag::factory()->translated(['en'], ['en' => ['slug' => 'laravel']])->create();
        $post = Post::factory()->published()->translated(['en'])->create(['post_category_id' => $used->id]);
        $post->tags()->attach($tag);

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.url('/en/news/category/insights').'</loc>', false);
        $response->assertSee('<loc>'.url('/en/news/tag/laravel').'</loc>', false);
        $response->assertDontSee('empty-category', false);
    }

    public function test_disabled_languages_are_left_out(): void
    {
        app(SiteSettings::class)->put([SettingKey::EnabledLocales->value => ['en', 'ar']]);

        $this->get('/sitemap.xml')->assertDontSee(url('/tr'), false);
    }

    public function test_new_content_appears_without_waiting_for_the_cache(): void
    {
        $this->get('/sitemap.xml')->assertDontSee('fresh-article', false);

        Post::factory()->published()->translated(['en'], ['en' => ['slug' => 'fresh-article']])->create();

        $this->get('/sitemap.xml')->assertSee('fresh-article', false);
    }
}
