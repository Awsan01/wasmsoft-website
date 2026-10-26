<?php

namespace Database\Factories;

use App\Models\ProjectCategory;
use App\Models\ProjectCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectCategoryTranslation>
 */
class ProjectCategoryTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->word();

        return [
            'project_category_id' => ProjectCategory::factory(),
            'locale' => 'en',
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
