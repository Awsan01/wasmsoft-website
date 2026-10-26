<?php

namespace App\Console\Commands;

use App\Content\ContentCache;
use App\Content\PublicationStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Carbon;

/**
 * Scheduled publications appear on their own — public queries compare the
 * publication date with the current time — but cached public content
 * (navigation, home page sections, sitemap) would only follow once its
 * cache expires. Run every minute by the scheduler, this refreshes that
 * cache as soon as something scheduled has gone live since the last run.
 */
#[Signature('wasmsoft:release-scheduled')]
#[Description('Refresh the cached public content when scheduled publications go live')]
class ReleaseScheduledContent extends Command
{
    private const CHECKED_UNTIL = 'wasmsoft:release-scheduled:checked-until';

    /** Content types with a publication date. */
    private const PUBLISHABLE = [Page::class, Service::class, Project::class, Post::class];

    public function handle(Cache $cache, ContentCache $content): int
    {
        $now = now();
        $checkedUntil = $cache->get(self::CHECKED_UNTIL);
        $since = is_string($checkedUntil) ? Carbon::parse($checkedUntil) : $now->copy()->subDay();

        $released = collect(self::PUBLISHABLE)->contains(fn (string $model): bool => $model::query()
            ->where('status', PublicationStatus::Published)
            ->where('published_at', '>', $since)
            ->where('published_at', '<=', $now)
            ->exists());

        if ($released) {
            $content->flush();
            $this->components->info('Scheduled content went live; the cached public content was refreshed.');
        }

        $cache->forever(self::CHECKED_UNTIL, $now->toIso8601String());

        return self::SUCCESS;
    }
}
