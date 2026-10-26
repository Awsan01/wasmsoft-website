<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\PublicationStatus;
use App\Content\PubliclyRoutable;
use App\Content\PublishableContent;
use App\Content\TranslatableContent;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A portfolio project, presented at /{locale}/projects/{slug}.
 *
 * @property int $id
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property int|null $project_category_id
 * @property int|null $cover_id
 * @property string|null $client_name
 * @property string|null $project_url
 * @property list<string>|null $technologies
 * @property Carbon|null $completed_on
 * @property bool $is_featured
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ProjectCategory|null $category
 * @property-read Media|null $cover
 * @property-read Collection<int, Media> $gallery
 * @property-read Collection<int, ProjectTranslation> $translations
 */
#[Fillable(['project_category_id', 'cover_id', 'client_name', 'project_url', 'technologies', 'completed_on', 'is_featured'])]
class Project extends Model implements Auditable, PubliclyRoutable, PublishableContent, TranslatableContent
{
    use FlushesContentCache, Publishable;

    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** @use HasTranslations<ProjectTranslation> */
    use HasTranslations;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'published_at' => null,
        'project_category_id' => null,
        'cover_id' => null,
        'client_name' => null,
        'project_url' => null,
        'technologies' => null,
        'completed_on' => null,
        'is_featured' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'technologies' => 'array',
            'completed_on' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProjectTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    /**
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    /**
     * @return BelongsToMany<Media, $this>
     */
    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)->withPivot('position')->orderByPivot('position');
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        if ($translation === null || ! $this->isPublished()) {
            return null;
        }

        return route('site.projects.show', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('title') ?? 'Project #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'title';
    }
}
