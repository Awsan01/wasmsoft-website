<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PageTranslation>
 */
class PageTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'page_id' => Page::factory(),
            'locale' => 'en',
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(12),
            'body' => fake()->paragraphs(2, true),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }
}
