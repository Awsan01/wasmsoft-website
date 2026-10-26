<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    /**
     * @param  list<string>  $locales
     * @param  array<string, array<string, mixed>>  $overrides
     */
    public function translated(array $locales = ['en'], array $overrides = []): static
    {
        $factory = $this;

        foreach ($locales as $locale) {
            $factory = $factory->has(
                TagTranslation::factory()->state(['locale' => $locale, ...($overrides[$locale] ?? [])]),
                'translations',
            );
        }

        return $factory;
    }
}
