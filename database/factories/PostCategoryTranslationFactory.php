<?php

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostCategoryTranslation>
 */
class PostCategoryTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->word();

        return [
            'post_category_id' => PostCategory::factory(),
            'locale' => 'en',
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
