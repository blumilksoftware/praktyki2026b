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
        $isPaid = fake()->boolean();
        $salaryMin = $isPaid ? fake()->numberBetween(3000, 6000) : null;

        return [
            "company_id" => Company::factory(),
            "title" => fake()->jobTitle(),
            "description" => fake()->paragraph(5),
            "spots" => fake()->numberBetween(1, 10),
            "city" => fake()->city(),
            "latitude" => fake()->latitude(),
            "longitude" => fake()->longitude(),
            "start_date" => $startDate,
            "end_date" => fake()->dateTimeBetween($startDate, $startDate->format("Y-m-d") . " +3 months"),
            "work_mode" => fake()->randomElement(WorkMode::cases()),
            "status" => OfferStatus::Published,
            "is_paid" => $isPaid,
            "salary_min" => $salaryMin,
            "salary_max" => $isPaid ? $salaryMin + fake()->numberBetween(500, 2000) : null,
        ];
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

    public function closed(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => OfferStatus::Closed,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => OfferStatus::Expired,
            "start_date" => now()->subMonth(),
            "end_date" => now()->subDay(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes): array {
            $salaryMin = fake()->numberBetween(3000, 6000);

            return [
                "is_paid" => true,
                "salary_min" => $salaryMin,
                "salary_max" => $salaryMin + fake()->numberBetween(500, 2000),
            ];
        });
    }

    public function unpaid(): static
    {
        return $this->state(fn(array $attributes): array => [
            "is_paid" => false,
            "salary_min" => null,
            "salary_max" => null,
        ]);
    }
}
