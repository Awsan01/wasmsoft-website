<?php

namespace Database\Factories;

use App\Media\MediaKind;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Media records without files on disk; tests that need real files upload
 * through App\Media\MediaLibrary with a fake storage disk.
 *
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uploaded_by' => null,
            'kind' => MediaKind::Image,
            'disk' => 'public',
            'path' => 'media/test/'.Str::lower((string) Str::ulid()).'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 120_000,
            'width' => 1200,
            'height' => 800,
            'variants' => [],
            'alt' => ['en' => fake()->sentence(4)],
        ];
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => MediaKind::Document,
            'path' => 'media/test/'.Str::lower((string) Str::ulid()).'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'width' => null,
            'height' => null,
            'alt' => null,
        ]);
    }
}
