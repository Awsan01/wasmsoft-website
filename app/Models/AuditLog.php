<?php

namespace App\Models;

use App\Audit\AuditEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * An immutable audit record. Written through App\Audit\AuditLogger only;
 * entries older than the configured retention period are pruned daily.
 *
 * @property int $id
 * @property int|null $user_id
 * @property AuditEvent $event
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $subject_label
 * @property array<string, mixed>|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'event' => AuditEvent::class,
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subDays((int) config('wasmsoft.audit.retention_days')));
    }
}
