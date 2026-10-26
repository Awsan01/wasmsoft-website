<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\ProjectTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One language version of a project. `body` is Markdown.
 *
 * @property int $id
 * @property int $project_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 */
#[Fillable(['locale', 'title', 'slug', 'summary', 'body', 'meta_title', 'meta_description'])]
class ProjectTranslation extends Translation
{
    /** @use HasFactory<ProjectTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
