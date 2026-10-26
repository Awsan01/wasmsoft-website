<?php

namespace App\Content;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The single write path for dashboard-managed content.
 *
 * Attributes, publication status, translations and related records are
 * saved in one database transaction, so a failure never leaves half-saved
 * content; the change is then audited and the public content cache cleared.
 */
class ContentWriter
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ContentCache $cache,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes  mass-assignable attributes
     * @param  array<string, array<string, mixed>>|null  $translations  locale => field => value
     * @param  (Closure(TModel): void)|null  $related  syncs relations (tags, gallery…) inside the transaction
     */
    public function save(
        Model $model,
        array $attributes,
        ?PublicationStatus $status = null,
        ?array $translations = null,
        ?Closure $related = null,
    ): void {
        $creating = ! $model->exists;
        $previousStatus = $model->getAttribute('status');

        DB::transaction(function () use ($model, $attributes, $status, $translations, $related, $creating, $previousStatus): void {
            $model->fill($attributes);

            if ($status !== null && $model instanceof PublishableContent) {
                $model->applyStatus($status);
            }

            $model->save();

            if ($translations !== null && $model instanceof TranslatableContent) {
                $model->syncTranslations($translations);
            }

            if ($related !== null) {
                $related($model);
            }

            // Translation or relation changes alone do not update the timestamp.
            $model->touch();

            $status = $model->getAttribute('status');

            $this->audit->record(
                $this->eventFor($model, $creating, $previousStatus),
                $model,
                $status instanceof PublicationStatus ? ['status' => $status->value] : [],
            );
        });

        $this->cache->flush();
    }

    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            // Recorded first, while the subject's title can still be read.
            $this->audit->record(AuditEvent::ContentDeleted, $model);
            $model->delete();
        });

        $this->cache->flush();
    }

    private function eventFor(Model $model, bool $created, mixed $previousStatus): AuditEvent
    {
        if ($created) {
            return AuditEvent::ContentCreated;
        }

        $status = $model->getAttribute('status');

        if (! $status instanceof PublicationStatus || $status === $previousStatus) {
            return AuditEvent::ContentUpdated;
        }

        return match ($status) {
            PublicationStatus::Published => AuditEvent::ContentPublished,
            PublicationStatus::Archived => AuditEvent::ContentArchived,
            PublicationStatus::Draft => AuditEvent::ContentUnpublished,
        };
    }
}
