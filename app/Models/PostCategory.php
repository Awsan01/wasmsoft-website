<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\PubliclyRoutable;
use App\Content\TranslatableContent;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Sortable;
use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * News category, with its own listing at /{locale}/news/category/{slug}.
 *
 * @property int $id
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PostCategoryTranslation> $translations
 */
class PostCategory extends Model implements Auditable, PubliclyRoutable, TranslatableContent
{
    use FlushesContentCache, Sortable;

    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory;

    /** @use HasTranslations<PostCategoryTranslation> */
    use HasTranslations;

    /**
     * @return HasMany<PostCategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PostCategoryTranslation::class);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        return $translation === null ? null : route('site.news.category', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('name') ?? 'News category #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'name';
    }
}
