<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\TranslatableContent;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Sortable;
use Database\Factories\ProjectCategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Category used to filter the projects listing (?category={slug}).
 *
 * @property int $id
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, ProjectCategoryTranslation> $translations
 */
class ProjectCategory extends Model implements Auditable, TranslatableContent
{
    use FlushesContentCache, Sortable;

    /** @use HasFactory<ProjectCategoryFactory> */
    use HasFactory;

    /** @use HasTranslations<ProjectCategoryTranslation> */
    use HasTranslations;

    /**
     * @return HasMany<ProjectCategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProjectCategoryTranslation::class);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('name') ?? 'Project category #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'name';
    }
}
