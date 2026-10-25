<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\PubliclyRoutable;
use App\Content\SystemPage;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A website page. Custom pages (About, Privacy…) have a slug per language
 * and are served at /{locale}/{slug}; system pages hold the editable copy
 * of application-defined pages (home hero, listing introductions).
 *
 * `system_key`, `status` and `published_at` are not mass assignable:
 * status changes go through applyStatus() after authorization.
 *
 * @property int $id
 * @property SystemPage|null $system_key
 * @property PageTemplate $template
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PageTranslation> $translations
 */
#[Fillable(['template'])]
class Page extends Model implements Auditable, PubliclyRoutable
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /** @use HasTranslations<PageTranslation> */
    use HasTranslations;

    use Publishable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'system_key' => null,
        'template' => 'default',
        'status' => 'draft',
        'published_at' => null,
    ];

    protected function casts(): array
    {
        return [
            'system_key' => SystemPage::class,
            'template' => PageTemplate::class,
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<PageTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function isSystem(): bool
    {
        return $this->system_key !== null;
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function custom(Builder $query): void
    {
        $query->whereNull('system_key');
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function system(Builder $query, SystemPage $page): void
    {
        $query->where('system_key', $page);
    }

    public function publicUrl(string $locale): ?string
    {
        $translation = $this->translation($locale);

        if ($translation === null || ! $this->isPublished()) {
            return null;
        }

        if ($this->system_key !== null) {
            return route($this->system_key->routeName(), ['locale' => $locale]);
        }

        return route('site.pages.show', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    public function auditLabel(): string
    {
        return $this->translations()->orderBy('id')->value('title') ?? 'Page #'.$this->getKey();
    }

    protected function slugSource(): ?string
    {
        return $this->isSystem() ? null : 'title';
    }
}
