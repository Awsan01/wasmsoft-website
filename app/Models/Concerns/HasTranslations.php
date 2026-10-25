<?php

namespace App\Models\Concerns;

use App\Content\Slug;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Content whose translatable fields live in a separate translation table
 * (pages → page_translations…): one row per language, created only when
 * that language version exists. A missing row *is* a missing translation;
 * nothing silently falls back to another language.
 *
 * Read translations from the eager-loaded relation (`withTranslation()`
 * on public pages, `with('translations')` in the dashboard); strict mode
 * reports any lazy loading outside production.
 *
 * @template TTranslation of Translation
 */
trait HasTranslations
{
    /**
     * @return HasMany<TTranslation, $this>
     */
    abstract public function translations(): HasMany;

    /**
     * Translation field a missing slug is generated from, or null when the
     * translation table has no slug column.
     */
    protected function slugSource(): ?string
    {
        return null;
    }

    /**
     * @return TTranslation|null
     */
    public function translation(?string $locale = null): ?Translation
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale);
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translation($locale) !== null;
    }

    /**
     * @return list<string>
     */
    public function translatedLocales(): array
    {
        return array_values($this->translations->map(fn (Translation $translation): string => $translation->locale)->all());
    }

    /**
     * Only records that exist in $locale.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function translatedIn(Builder $query, string $locale): void
    {
        $query->whereHas('translations', fn (Builder $translation) => $translation->where('locale', $locale));
    }

    /**
     * Eager-load only the $locale translation (public listings).
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function withTranslation(Builder $query, string $locale): void
    {
        $query->with(['translations' => fn (HasMany $translation) => $translation->where('locale', $locale)]);
    }

    /**
     * Create, update or delete translation rows from validated form input
     * (locale => field => value). A locale whose fields are all empty has
     * its translation removed, which is how editors delete a language version.
     *
     * @param  array<string, array<string, mixed>>  $input
     */
    public function syncTranslations(array $input): void
    {
        $existing = $this->translations()->get()->keyBy('locale');

        foreach ($input as $locale => $values) {
            unset($values['locale']);
            $values = array_map(fn (mixed $value): mixed => is_string($value) && trim($value) === '' ? null : $value, $values);
            $translation = $existing->get($locale);

            if (array_filter($values, fn (mixed $value): bool => $value !== null && $value !== []) === []) {
                $translation?->delete();

                continue;
            }

            $translation ??= $this->translations()->make(['locale' => $locale]);
            $translation->fill($values);

            if (($source = $this->slugSource()) !== null && blank($translation->getAttribute('slug'))) {
                $slug = Slug::make((string) $translation->getAttribute($source), $locale);
                $translation->setAttribute('slug', Slug::unique(
                    $slug === '' ? 'item' : $slug,
                    fn (string $candidate): bool => $this->slugTaken($candidate, $locale, $translation),
                ));
            }

            $translation->save();
        }

        $this->unsetRelation('translations');
    }

    /**
     * Whether $slug is used by another translation of the same content type in $locale.
     */
    public function slugTaken(string $slug, string $locale, ?Translation $except = null): bool
    {
        $query = $this->translations()->getRelated()->newQuery()
            ->where('locale', $locale)
            ->where('slug', $slug);

        if ($except !== null && $except->exists) {
            $query->whereKeyNot($except->getKey());
        }

        return $query->exists();
    }
}
