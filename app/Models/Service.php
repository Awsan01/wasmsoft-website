<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\ContentIcon;
use App\Content\PublicationStatus;
use App\Content\PubliclyRoutable;
use App\Content\PublishableContent;
use App\Content\TranslatableContent;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Sortable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A service Wasmsoft offers, presented at /{locale}/services/{slug}.
 *
 * @property int $id
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property int $position
 * @property ContentIcon|null $icon
 * @property int|null $image_id
 * @property bool $is_featured
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Media|null $image
 * @property-read Collection<int, ServiceTranslation> $translations
 */
#[Fillable(['icon', 'image_id', 'is_featured'])]
class Service extends Model implements Auditable, PubliclyRoutable, PublishableContent, TranslatableContent
{
    use FlushesContentCache, Publishable, Sortable;

    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /** @use HasTranslations<ServiceTranslation> */
    use HasTranslations;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'published_at' => null,
        'icon' => null,
        'image_id' => null,
        'is_featured' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'position' => 'integer',
            'icon' => ContentIcon::class,
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ServiceTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        if ($translation === null || ! $this->isPublished()) {
            return null;
        }

        return route('site.services.show', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('title') ?? 'Service #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'title';
    }
}
