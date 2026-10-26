<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\TagTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tag_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 */
#[Fillable(['locale', 'name', 'slug'])]
class TagTranslation extends Translation
{
    /** @use HasFactory<TagTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @return BelongsTo<Tag, $this>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
