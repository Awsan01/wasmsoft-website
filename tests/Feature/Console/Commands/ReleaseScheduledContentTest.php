<?php

namespace Tests\Feature\Console\Commands;

use App\Content\ContentCache;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReleaseScheduledContentTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function cachedValue(): string
    {
        return app(ContentCache::class)->remember('test-entry', fn (): string => 'value computed at '.now()->toIso8601String());
    }

    public function test_cached_content_is_refreshed_when_a_scheduled_article_goes_live(): void
    {
        Post::factory()->scheduled()->translated(['en'])->create(['published_at' => now()->addMinutes(5)]);
        $this->artisan('wasmsoft:release-scheduled')->assertSuccessful();
        $before = $this->cachedValue();

        $this->travel(10)->minutes();
        $this->artisan('wasmsoft:release-scheduled')->assertSuccessful();

        $this->assertNotSame($before, $this->cachedValue());
    }

    public function test_cached_content_is_kept_when_nothing_went_live(): void
    {
        Post::factory()->scheduled()->translated(['en'])->create(['published_at' => now()->addDay()]);
        $this->artisan('wasmsoft:release-scheduled')->assertSuccessful();
        $before = $this->cachedValue();

        $this->travel(10)->minutes();
        $this->artisan('wasmsoft:release-scheduled')->assertSuccessful();

        $this->assertSame($before, $this->cachedValue());
    }
}
