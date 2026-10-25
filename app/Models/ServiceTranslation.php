<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\ServiceTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One language version of a service. `body` is Markdown.
 *
 * @property int $id
 * @property int $service_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $body
 * @property list<string>|null $benefits
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['locale', 'title', 'slug', 'summary', 'body', 'benefits', 'meta_title', 'meta_description'])]
class ServiceTranslation extends Translation
{
    /** @use HasFactory<ServiceTranslationFactory> */
    use FlushesContentCache, HasFactory;

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
