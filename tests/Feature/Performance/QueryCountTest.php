<?php

namespace Tests\Feature\Performance;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Lists must load related records in bulk: a page showing seven items
 * runs exactly as many queries as the same page showing two. (Lazy
 * loading already throws outside production; this also catches explicit
 * queries made once per item.)
 */
class QueryCountTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function publicPages(): array
    {
        return [
            'home' => ['/en'],
            'services' => ['/en/services'],
            'projects' => ['/en/projects'],
            'news' => ['/en/news'],
            'sitemap' => ['/sitemap.xml'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dashboardPages(): array
    {
        return [
            'overview' => ['/admin'],
            'pages' => ['/admin/pages'],
            'services' => ['/admin/services'],
            'projects' => ['/admin/projects'],
            'news' => ['/admin/posts'],
            'media' => ['/admin/media'],
            'inquiries' => ['/admin/inquiries'],
            'users' => ['/admin/users'],
            'audit log' => ['/admin/audit'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_run_as_many_queries_for_many_items_as_for_few(string $url): void
    {
        $this->assertQueriesDoNotGrowWithContent($url);
    }

    #[DataProvider('dashboardPages')]
    public function test_dashboard_lists_run_as_many_queries_for_many_items_as_for_few(string $url): void
    {
        $this->actingAs(User::factory()->administrator()->create());

        $this->assertQueriesDoNotGrowWithContent($url);
    }

    private function assertQueriesDoNotGrowWithContent(string $url): void
    {
        $this->addContent(2);
        // Warm up what is resolved once per process, so both measurements start equal.
        $this->get($url)->assertOk();
        $few = $this->queriesFor($url);

        $this->addContent(5);
        $many = $this->queriesFor($url);

        $this->assertSame($few, $many, "{$url} ran {$few} queries with 2 items of each kind but {$many} with 7.");
    }

    /**
     * Queries for one request with an empty cache (the worst case).
     */
    private function queriesFor(string $url): int
    {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get($url)->assertOk();

        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }

    /**
     * Published, featured English content with the relations lists display.
     */
    private function addContent(int $count): void
    {
        $author = User::factory()->author()->create();
        $postCategory = PostCategory::factory()->translated(['en'])->create();
        $projectCategory = ProjectCategory::factory()->translated(['en'])->create();
        $tag = Tag::factory()->translated(['en'])->create();
        $audit = app(AuditLogger::class);

        for ($i = 0; $i < $count; $i++) {
            Service::factory()->published()->featured()->translated(['en'])->create(['image_id' => Media::factory()->create()->id]);

            $project = Project::factory()->published()->featured()->translated(['en'])->create([
                'project_category_id' => $projectCategory->id,
                'cover_id' => Media::factory()->create()->id,
            ]);
            $project->gallery()->attach(Media::factory()->count(2)->create());

            $post = Post::factory()->published()->featured()->by($author)->translated(['en'])->create([
                'post_category_id' => $postCategory->id,
                'cover_id' => Media::factory()->create()->id,
            ]);
            $post->tags()->attach($tag);

            Page::factory()->published()->translated(['en'])->create();
            Inquiry::factory()->create();
            $audit->record(AuditEvent::ContentUpdated, $post, actor: $author);
        }
    }
}
