<?php

namespace App\Content;

use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Cache for public content (navigation, sitemap, home-page sections).
 *
 * Every key embeds a version number that any content change increments,
 * so one save invalidates all cached content at once on every cache store
 * (no cache tags needed). Superseded entries simply expire.
 */
class ContentCache
{
    private const VERSION_KEY = 'content-cache:version';

    public function __construct(private readonly Cache $cache) {}

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $key, Closure $callback): mixed
    {
        return $this->cache->remember(
            "content:{$this->version()}:{$key}",
            (int) config('wasmsoft.cache_ttl'),
            $callback,
        );
    }

    public function flush(): void
    {
        $this->cache->add(self::VERSION_KEY, 1);
        $this->cache->increment(self::VERSION_KEY);
    }

    private function version(): int
    {
        return (int) $this->cache->rememberForever(self::VERSION_KEY, fn (): int => 1);
    }
}
