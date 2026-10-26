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
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A news article, presented at /{locale}/news/{slug}.
 *
 * Authors write drafts; publishing — and scheduling with a future
 * publication date — is up to editors (see PostPolicy).
 *
 * @property int $id
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property int|null $author_id
 * @property int|null $post_category_id
 * @property int|null $cover_id
 * @property bool $is_featured
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $author
 * @property-read PostCategory|null $category
 * @property-read Media|null $cover
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, PostTranslation> $translations
 */
#[Fillable(['published_at', 'author_id', 'post_category_id', 'cover_id', 'is_featured'])]
class Post extends Model implements Auditable, PubliclyRoutable, PublishableContent, TranslatableContent
{
    use FlushesContentCache, Publishable;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /** @use HasTranslations<PostTranslation> */
    use HasTranslations;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'published_at' => null,
        'author_id' => null,
        'post_category_id' => null,
        'cover_id' => null,
        'is_featured' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PostTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function isWrittenBy(User $user): bool
    {
        return $this->author_id !== null && $this->author_id === $user->id;
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        if ($translation === null || ! $this->isPublished()) {
            return null;
        }

        return route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('title') ?? 'Post #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return 'title';
    }
}
