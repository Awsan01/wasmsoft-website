<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TagTranslation>
 */
class TagTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();

        return [
            'tag_id' => Tag::factory(),
            'locale' => 'en',
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
