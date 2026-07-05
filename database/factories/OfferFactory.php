<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OfferStatus;
use App\Enums\WorkMode;
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
        $startDate = fake()->dateTimeBetween("+1 week", "+2 months");

        return [
            "company_id" => Company::factory(),
            "title" => fake()->jobTitle(),
            "description" => fake()->paragraph(5),
            "spots" => fake()->numberBetween(1, 10),
            "is_active" => true,
            "city" => fake()->city(),
            "latitude" => fake()->latitude(),
            "longitude" => fake()->longitude(),
            "start_date" => $startDate,
            "end_date" => fake()->dateTimeBetween($startDate, $startDate->format("Y-m-d") . " +3 months"),
            "work_mode" => fake()->randomElement(WorkMode::cases()),
            "status" => OfferStatus::Published,
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

    public function draft(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => OfferStatus::Draft,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => OfferStatus::Published,
        ]);
    }
}
