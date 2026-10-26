<?php

namespace Database\Factories;

use App\Inquiries\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale' => 'en',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'company' => null,
            'message' => fake()->paragraph(),
            'consented_at' => now(),
        ];
    }

    public function status(InquiryStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function spam(): static
    {
        return $this->status(InquiryStatus::Spam);
    }
}
