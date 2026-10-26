<?php

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Database\Factories\PostTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One language version of a news article. `body` is Markdown; `summary`
 * is the plain-text excerpt shown on cards and under the title.
 *
 * @property int $id
 * @property int $post_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 */
#[Fillable(['locale', 'title', 'slug', 'summary', 'body', 'meta_title', 'meta_description'])]
class PostTranslation extends Translation
{
    /** @use HasFactory<PostTranslationFactory> */
    use FlushesContentCache, HasFactory;

    /** Average reading speed used for the reading-time estimate. */
    private const WORDS_PER_MINUTE = 200;

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Estimated reading time of the article in whole minutes (at least one).
     */
    public function readingMinutes(): int
    {
        $words = preg_match_all('/[\p{L}\p{N}]+/u', (string) $this->body);

        return max(1, (int) ceil(((int) $words) / self::WORDS_PER_MINUTE));
    }
}
