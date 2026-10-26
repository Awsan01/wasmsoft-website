<?php

namespace Database\Factories;

use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->numberBetween(2010, 2026),
            'title' => ['en' => ucfirst(fake()->word()).' '.fake()->word().' '.fake()->word()],
            'description' => ['en' => fake()->sentence(14)],
            'is_visible' => true,
        ];
    }
}
