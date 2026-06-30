<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            "company_id" => Company::factory(),
            "title" => fake()->jobTitle(),
            "description" => fake()->paragraph(5),
            "spots" => fake()->numberBetween(1, 10),
            "is_active" => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes): array => [
            "is_active" => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes): array => [
            "is_active" => false,
        ]);
    }
}
