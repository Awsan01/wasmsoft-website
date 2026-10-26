<?php

namespace App\Models;

use App\Audit\AuditEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

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
 * @property-read User|null $user
 * @property-read Model|null $subject
 */
class AuditLog extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    /** Dashboard page of each kind of subject (morph alias => route name). */
    private const SUBJECT_ROUTES = [
        'page' => 'admin.pages.edit',
        'service' => 'admin.services.edit',
        'project' => 'admin.projects.edit',
        'project_category' => 'admin.project-categories.edit',
        'post' => 'admin.posts.edit',
        'post_category' => 'admin.post-categories.edit',
        'tag' => 'admin.tags.edit',
        'media' => 'admin.media.edit',
        'user' => 'admin.users.edit',
        'inquiry' => 'admin.inquiries.show',
    ];

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

    /**
     * The dashboard page of the subject, while it still exists.
     */
    public function subjectUrl(): ?string
    {
        $route = self::SUBJECT_ROUTES[$this->subject_type] ?? null;

        if ($route === null || $this->subject === null || ! Route::has($route)) {
            return null;
        }

        return route($route, $this->subject);
    }

    /**
     * The recorded properties as short, readable lines.
     *
     * @return list<string>
     */
    public function details(): array
    {
        $properties = $this->properties ?? [];
        $lines = [];

        if (isset($properties['from'], $properties['to'])) {
            $lines[] = $this->phrase('from_to', ['from' => $this->text($properties['from']), 'to' => $this->text($properties['to'])]);
        }

        if (isset($properties['changed']) && is_array($properties['changed'])) {
            $lines[] = $this->phrase('changed', ['fields' => implode(', ', array_map($this->text(...), $properties['changed']))]);
        }

        foreach (Arr::except($properties, ['from', 'to', 'changed']) as $key => $value) {
            if ($value === null) {
                continue;
            }

            $lines[] = match ($key) {
                'remember' => $value === true ? $this->phrase('remember') : null,
                'source' => $value === 'console' ? $this->phrase('console') : null,
                default => $this->phrase('value', [
                    'name' => Lang::has("admin.audit.details.names.{$key}") ? $this->phrase("names.{$key}") : (string) $key,
                    'value' => $this->text($value),
                ]),
            };
        }

        return array_values(array_filter($lines, fn (?string $line): bool => $line !== null && $line !== ''));
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function phrase(string $key, array $replace = []): string
    {
        $phrase = __("admin.audit.details.{$key}", $replace);

        return is_string($phrase) ? $phrase : $key;
    }

    private function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
