<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\ContentBlock;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasLocalizedText;
use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A moment in the company history (about page), ordered by year.
 *
 * @property int $id
 * @property int $year
 * @property array<string, string> $title
 * @property array<string, string>|null $description
 * @property bool $is_visible
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['year', 'title', 'description', 'is_visible'])]
class Milestone extends Model implements Auditable, ContentBlock
{
    use FlushesContentCache, HasLocalizedText;

    /** @use HasFactory<MilestoneFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'description' => null,
        'is_visible' => true,
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'title' => 'array',
            'description' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function chronological(Builder $query): void
    {
        $query->orderBy('year')->orderBy('id');
    }

    /**
     * Visible milestones written in $locale, oldest first.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function shownIn(Builder $query, string $locale): void
    {
        $query->where('is_visible', true)->writtenIn('title', $locale)->chronological();
    }

    public function blockLabel(): ?string
    {
        $title = $this->displayText('title');

        return $title === null ? (string) $this->year : $this->year.' · '.$title;
    }

    public function blockDetail(): ?string
    {
        $description = $this->displayText('description');

        return $description === null ? null : Str::limit($description, 90);
    }

    public function blockImage(): ?Media
    {
        return null;
    }

    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    public function translatedLocales(): array
    {
        return $this->localesWith('title');
    }

    public function auditLabel(): string
    {
        return $this->blockLabel() ?? 'Milestone #'.$this->getKey();
    }
}
