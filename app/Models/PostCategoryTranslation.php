<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\PostCategoryTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $post_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 */
#[Fillable(['locale', 'name', 'slug'])]
class PostCategoryTranslation extends Translation
{
    /** @use HasFactory<PostCategoryTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }
}
