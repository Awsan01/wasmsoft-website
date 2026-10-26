<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\ContentBlock;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasLocalizedText;
use App\Models\Concerns\Sortable;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A person presented on the about page. The name is the same in every
 * language; the role and short biography are written per language.
 *
 * @property int $id
 * @property string $name
 * @property array<string, string> $role
 * @property array<string, string>|null $bio
 * @property int|null $photo_id
 * @property bool $is_visible
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Media|null $photo
 */
#[Fillable(['name', 'role', 'bio', 'photo_id', 'is_visible'])]
class TeamMember extends Model implements Auditable, ContentBlock
{
    use FlushesContentCache, HasLocalizedText, Sortable;

    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'bio' => null,
        'photo_id' => null,
        'is_visible' => true,
    ];

    protected function casts(): array
    {
        return [
            'role' => 'array',
            'bio' => 'array',
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
     * Visible people whose role is written in $locale, in display order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function shownIn(Builder $query, string $locale): void
    {
        $query->where('is_visible', true)->writtenIn('role', $locale)->ordered();
    }

    public function blockLabel(): ?string
    {
        return $this->name;
    }

    public function blockDetail(): ?string
    {
        return $this->displayText('role');
    }

    public function blockImage(): ?Media
    {
        return $this->photo;
    }

    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    public function translatedLocales(): array
    {
        return $this->localesWith('role');
    }

    public function auditLabel(): string
    {
        return $this->name;
    }
}
