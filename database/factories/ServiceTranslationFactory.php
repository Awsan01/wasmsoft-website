<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceTranslation>
 */
class ServiceTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'service_id' => Service::factory(),
            'locale' => 'en',
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(14),
            'body' => fake()->paragraphs(2, true),
            'benefits' => [fake()->sentence(5), fake()->sentence(6)],
            'meta_title' => null,
            'meta_description' => null,
        ];
    }
}
