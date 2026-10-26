<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Inquiries\InquiryStatus;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A message sent through the public contact form.
 *
 * It holds personal data: it is only shown to users who handle inquiries,
 * never written to the audit log, and deleted automatically after the
 * retention period (spam much sooner). `status`, the internal note and
 * the handling fields are not mass assignable.
 *
 * @property int $id
 * @property InquiryStatus $status
 * @property string $locale
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property int|null $service_id
 * @property string $message
 * @property string|null $internal_note
 * @property Carbon $consented_at
 * @property int|null $handled_by
 * @property Carbon|null $handled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Service|null $service
 * @property-read User|null $handler
 */
#[Fillable(['locale', 'name', 'email', 'phone', 'company', 'service_id', 'message', 'consented_at'])]
class Inquiry extends Model implements Auditable
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory, Prunable;

    /** Spam is kept briefly, in case a real message was caught by mistake. */
    public const SPAM_RETENTION_DAYS = 30;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
        'phone' => null,
        'company' => null,
        'service_id' => null,
        'internal_note' => null,
        'handled_by' => null,
        'handled_at' => null,
    ];

    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
            'consented_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Who last changed the status.
     *
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<', now()->subDays((int) config('wasmsoft.contact.retention_days')))
            ->orWhere(fn (Builder $query) => $query
                ->where('status', InquiryStatus::Spam)
                ->where('created_at', '<', now()->subDays(self::SPAM_RETENTION_DAYS)));
    }

    /**
     * Audit entries name the inquiry by number only: no personal data.
     */
    public function auditLabel(): string
    {
        return 'Inquiry #'.$this->getKey();
    }
}
