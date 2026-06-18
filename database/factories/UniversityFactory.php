<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UniversityVerificationStatus;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<University>
 */
class UniversityFactory extends Factory
{
    public function definition(): array
    {
        return [
            "name" => fake()->company() . " University",
            "email" => fake()->unique()->companyEmail(),
            "domain" => fake()->unique()->domainName(),
            "street" => fake()->streetName(),
            "building_number" => fake()->buildingNumber(),
            "postal_code" => fake()->postcode(),
            "city" => fake()->city(),
            "phone" => fake()->phoneNumber(),
            "website" => fake()->optional()->url(),
            "verification_status" => UniversityVerificationStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes): array => [
            "verification_status" => UniversityVerificationStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes): array => [
            "verification_status" => UniversityVerificationStatus::Verified,
        ]);
    }
}
