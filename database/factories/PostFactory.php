<?php

namespace Database\Factories;

use App\Content\PublicationStatus;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => PublicationStatus::Draft,
            'published_at' => null,
            'author_id' => null,
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Published with a publication date in the future.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->addDay(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }

    public function by(User $author): static
    {
        return $this->state(fn (array $attributes) => ['author_id' => $author->id]);
    }

    /**
     * @param  list<string>  $locales
     * @param  array<string, array<string, mixed>>  $overrides
     */
    public function translated(array $locales = ['en'], array $overrides = []): static
    {
        $factory = $this;

        foreach ($locales as $locale) {
            $factory = $factory->has(
                PostTranslation::factory()->state(['locale' => $locale, ...($overrides[$locale] ?? [])]),
                'translations',
            );
        }

        return $factory;
    }
}
