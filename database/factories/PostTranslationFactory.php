<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostTranslation>
 */
class PostTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(5), '.');

        return [
            'post_id' => Post::factory(),
            'locale' => 'en',
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(18),
            'body' => fake()->paragraphs(4, true),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }
}
