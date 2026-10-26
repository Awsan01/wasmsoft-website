<?php

namespace Database\Factories;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Highlight>
 */
class HighlightFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => HighlightGroup::Differentiator,
            'title' => ['en' => ucfirst(fake()->unique()->word()).' '.fake()->word().' '.fake()->word()],
            'description' => ['en' => fake()->sentence(12)],
            'is_visible' => true,
        ];
    }

    public function group(HighlightGroup $group): static
    {
        return $this->state(fn (array $attributes) => ['group' => $group]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_visible' => false]);
    }
}
