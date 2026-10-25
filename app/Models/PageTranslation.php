<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\PageTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One language version of a page. `body` is Markdown (see App\Content\Markdown).
 *
 * @property int $id
 * @property int $page_id
 * @property string $locale
 * @property string $title
 * @property string|null $slug
 * @property string|null $summary
 * @property string|null $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['locale', 'title', 'slug', 'summary', 'body', 'meta_title', 'meta_description'])]
class PageTranslation extends Translation
{
    /** @use HasFactory<PageTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
