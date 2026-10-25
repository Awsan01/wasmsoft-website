<?php

namespace App\Content;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Content stored with one translation row per language (see Models\Concerns\HasTranslations).
 */
interface TranslatableContent
{
    /**
     * @return HasMany<covariant Translation, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function translations(): HasMany;

    /**
     * @param  array<string, array<string, mixed>>  $input  locale => field => value
     */
    public function syncTranslations(array $input): void;

    /**
     * @return list<string>
     */
    public function translatedLocales(): array;

    public function slugTaken(string $slug, string $locale, ?Translation $except = null): bool;
}
