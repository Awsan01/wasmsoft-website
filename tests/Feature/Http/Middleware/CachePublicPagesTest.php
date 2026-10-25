<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CachePublicPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_pages_are_shared_cacheable_without_cookies(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertHeader('Cache-Control', 'max-age=0, public, s-maxage=300');
        $this->assertNotNull($response->getEtag());
        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_matching_etag_returns_not_modified(): void
    {
        $etag = $this->get('/en')->getEtag();

        $response = $this->get('/en', ['If-None-Match' => $etag]);

        $response->assertStatus(304);
    }

    public function test_shared_caching_can_be_disabled(): void
    {
        config(['wasmsoft.http_cache_seconds' => 0]);

        $response = $this->get('/en');

        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }
}
