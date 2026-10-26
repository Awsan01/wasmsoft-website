<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote' => ['en' => fake()->sentence(20)],
            'author_name' => fake()->name(),
            'author_role' => ['en' => fake()->jobTitle()],
            'company' => fake()->company(),
            'has_consent' => false,
            'is_visible' => false,
        ];
    }

    /**
     * Agreed to be quoted and shown on the website.
     */
    public function shown(): static
    {
        return $this->state(fn (array $attributes) => ['has_consent' => true, 'is_visible' => true]);
    }
}
