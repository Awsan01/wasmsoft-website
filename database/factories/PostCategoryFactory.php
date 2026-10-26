<?php

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostCategory>
 */
class PostCategoryFactory extends Factory
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
                PostCategoryTranslation::factory()->state(['locale' => $locale, ...($overrides[$locale] ?? [])]),
                'translations',
            );
        }

        return $factory;
    }
}
