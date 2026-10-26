<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\ProjectCategoryTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $project_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 */
#[Fillable(['locale', 'name', 'slug'])]
class ProjectCategoryTranslation extends Translation
{
    /** @use HasFactory<ProjectCategoryTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
