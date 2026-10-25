<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * "Contains" search for dashboard and public listings.
 *
 * LIKE wildcards in the visitor's term are escaped, so "100%" matches
 * literally. The escape character is bound as a parameter, which works
 * the same on SQLite, MySQL and PostgreSQL. Column names must be literal
 * strings from code, never values from the request.
 */
final class Search
{
    public static function pattern(string $term): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
    }

    /**
     * Restrict $query to rows where any of $columns contains $term.
     *
     * @param  EloquentBuilder<*>|QueryBuilder  $query
     * @param  list<literal-string>  $columns
     */
    public static function apply(EloquentBuilder|QueryBuilder $query, array $columns, string $term): void
    {
        $pattern = self::pattern($term);

        $query->where(function (EloquentBuilder|QueryBuilder $query) use ($columns, $pattern): void {
            foreach ($columns as $column) {
                $query->orWhereRaw($column.' like ? escape ?', [$pattern, '\\']);
            }
        });
    }
}
