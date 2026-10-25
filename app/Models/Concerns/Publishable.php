<?php

namespace App\Models\Concerns;

use App\Content\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Draft / published / archived content with a publication date.
 *
 * Content is public only when it is published *and* its publication date
 * has passed, so posts can be scheduled. Models cast `status` to
 * App\Content\PublicationStatus and `published_at` to a datetime.
 */
trait Publishable
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublicationStatus::Published)
            ->whereNotNull($this->qualifyColumn('published_at'))
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && ! $this->published_at->isFuture();
    }

    public function isScheduled(): bool
    {
        return $this->status === PublicationStatus::Published && $this->published_at?->isFuture() === true;
    }

    /**
     * Apply a status chosen in the dashboard. Publishing keeps an existing
     * (possibly future) publication date and otherwise uses "now".
     */
    public function applyStatus(PublicationStatus $status): void
    {
        $this->status = $status;

        if ($status === PublicationStatus::Published && $this->published_at === null) {
            $this->published_at = now();
        }
    }
}
