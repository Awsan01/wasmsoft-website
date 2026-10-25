<?php

namespace App\Models\Concerns;

use App\Content\ContentCache;

/**
 * Invalidates the public content cache whenever the model is saved or
 * deleted (including translation rows and pivot-backed models).
 */
trait FlushesContentCache
{
    public static function bootFlushesContentCache(): void
    {
        static::saved(fn () => app(ContentCache::class)->flush());
        static::deleted(fn () => app(ContentCache::class)->flush());
    }
}
