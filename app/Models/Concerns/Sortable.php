<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Manually ordered records (`position` column). New records go last;
 * editors move items one step up or down, which works with a keyboard and
 * without JavaScript. Models with separate lists (menu locations,
 * highlight groups) narrow sortableGroup().
 */
trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function (self $model): void {
            if ($model->getAttribute('position') === null) {
                $model->setAttribute('position', (int) $model->sortableGroup()->max('position') + 1);
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('position'))->orderBy($this->qualifyColumn('id'));
    }

    /**
     * The records this one is ordered among.
     *
     * @return Builder<static>
     */
    protected function sortableGroup(): Builder
    {
        return static::query();
    }

    /**
     * Swap places with the neighbouring record (-1: up, +1: down).
     * Positions are renumbered first, so duplicates or gaps cannot block a move.
     */
    public function move(int $direction): void
    {
        DB::transaction(function () use ($direction): void {
            $siblings = $this->sortableGroup()->ordered()->lockForUpdate()->get()->values();
            $index = $siblings->search(fn (self $sibling): bool => $sibling->is($this));
            $target = $index === false ? null : $index + ($direction < 0 ? -1 : 1);

            if ($target === null || ! $siblings->has($target)) {
                return;
            }

            $ids = $siblings->modelKeys();
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

            foreach ($ids as $position => $id) {
                static::query()->whereKey($id)->update(['position' => $position + 1]);
            }

            $this->refresh();
        });
    }
}
