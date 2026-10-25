<?php

namespace Database\Factories;

use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template' => PageTemplate::Default,
            'status' => PublicationStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Archived,
            'published_at' => now()->subMonth(),
        ]);
    }

    public function about(): static
    {
        return $this->state(fn (array $attributes) => ['template' => PageTemplate::About]);
    }

    public function system(SystemPage $page): static
    {
        return $this->published()->state(fn (array $attributes) => ['system_key' => $page]);
    }

    /**
     * Add one translation per locale; $overrides holds per-locale attributes.
     *
     * @param  list<string>  $locales
     * @param  array<string, array<string, mixed>>  $overrides
     */
    public function translated(array $locales = ['en'], array $overrides = []): static
    {
        $factory = $this;

        foreach ($locales as $locale) {
            $factory = $factory->has(
                PageTranslation::factory()->state(['locale' => $locale, ...($overrides[$locale] ?? [])]),
                'translations',
            );
        }

        return $factory;
    }
}
