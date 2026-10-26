<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectTranslation>
 */
class ProjectTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'project_id' => Project::factory(),
            'locale' => 'en',
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(16),
            'body' => fake()->paragraphs(3, true),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }
}
