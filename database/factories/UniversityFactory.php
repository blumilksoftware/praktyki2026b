<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VerificationStatus;
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
            "address" => fake()->address(),
            "phone" => fake()->phoneNumber(),
            "website" => fake()->optional()->url(),
            "verification_status" => VerificationStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes): array => [
            "verification_status" => VerificationStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes): array => [
            "verification_status" => VerificationStatus::Verified,
        ]);
    }
}
