<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\PubliclyRoutable;
use App\Content\TranslatableContent;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasTranslations;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * News tag, with its own listing at /{locale}/news/tag/{slug}. Tags are
 * listed alphabetically, so they have no manual order.
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, TagTranslation> $translations
 */
class Tag extends Model implements Auditable, PubliclyRoutable, TranslatableContent
{
    use FlushesContentCache;

    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /** @use HasTranslations<TagTranslation> */
    use HasTranslations;

    /**
     * @return HasMany<TagTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        return $translation === null ? null : route('site.news.tag', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('name') ?? 'Tag #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'name';
    }
}
