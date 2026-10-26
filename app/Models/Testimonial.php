<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\ContentBlock;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasLocalizedText;
use App\Models\Concerns\Sortable;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A client quote. It is shown only when it is visible *and* the person
 * agreed to be quoted by name; quotes are never invented or edited into
 * something the client did not say.
 *
 * @property int $id
 * @property array<string, string> $quote
 * @property string $author_name
 * @property array<string, string>|null $author_role
 * @property string|null $company
 * @property int|null $photo_id
 * @property bool $has_consent
 * @property bool $is_visible
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Media|null $photo
 */
#[Fillable(['quote', 'author_name', 'author_role', 'company', 'photo_id', 'has_consent', 'is_visible'])]
class Testimonial extends Model implements Auditable, ContentBlock
{
    use FlushesContentCache, HasLocalizedText, Sortable;

    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'author_role' => null,
        'company' => null,
        'photo_id' => null,
        'has_consent' => false,
        'is_visible' => false,
    ];

    protected function casts(): array
    {
        return [
            'quote' => 'array',
            'author_role' => 'array',
            'has_consent' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    /**
     * Visible, consented quotes written in $locale, in display order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function shownIn(Builder $query, string $locale): void
    {
        $query->where('is_visible', true)->where('has_consent', true)->writtenIn('quote', $locale)->ordered();
    }

    public function blockLabel(): ?string
    {
        return $this->author_name;
    }

    public function blockDetail(): ?string
    {
        $quote = $this->displayText('quote');

        return $quote === null ? null : Str::limit($quote, 90);
    }

    public function blockImage(): ?Media
    {
        return $this->photo;
    }

    public function isVisible(): bool
    {
        return $this->is_visible && $this->has_consent;
    }

    public function translatedLocales(): array
    {
        return $this->localesWith('quote');
    }

    public function auditLabel(): string
    {
        return 'Testimonial #'.$this->getKey();
    }
}
