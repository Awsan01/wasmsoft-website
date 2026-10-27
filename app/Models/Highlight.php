<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Content\ContentBlock;
use App\Content\ContentIcon;
use App\Content\HighlightGroup;
use App\Models\Concerns\FlushesContentCache;
use App\Models\Concerns\HasLocalizedText;
use App\Models\Concerns\Sortable;
use Database\Factories\HighlightFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A differentiator, value or metric (see HighlightGroup). Ordered within
 * its group. Metrics show a figure ("12", "98 %") only when the company
 * supplies a real one: nothing is shown otherwise.
 *
 * @property int $id
 * @property HighlightGroup $group
 * @property ContentIcon|null $icon
 * @property string|null $figure
 * @property array<string, string> $title
 * @property array<string, string>|null $description
 * @property bool $is_visible
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['group', 'icon', 'figure', 'title', 'description', 'is_visible'])]
class Highlight extends Model implements Auditable, ContentBlock
{
    use FlushesContentCache, HasLocalizedText, Sortable;

    /** @use HasFactory<HighlightFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'icon' => null,
        'figure' => null,
        'description' => null,
        'is_visible' => true,
    ];

    protected function casts(): array
    {
        return [
            'group' => HighlightGroup::class,
            'icon' => ContentIcon::class,
            'title' => 'array',
            'description' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Visible items of $group that are written in $locale, in display order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function shownIn(Builder $query, HighlightGroup $group, string $locale): void
    {
        $query->where('group', $group)
            ->where('is_visible', true)
            ->writtenIn('title', $locale)
            ->ordered();
    }

    /**
     * @return Builder<static>
     */
    protected function sortableGroup(): Builder
    {
        return static::query()->where('group', $this->group);
    }

    public function blockLabel(): ?string
    {
        return $this->displayText('title');
    }

    public function blockDetail(): ?string
    {
        return $this->figure;
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
        return $this->displayText('title') ?? 'Highlight #'.$this->getKey();
    }
}
