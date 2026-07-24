<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partnership>
 */
class PartnershipFactory extends Factory
{
    protected $model = Partnership::class;

    public function definition(): array
    {
        return [
            "company_id" => Company::factory(),
            "university_id" => University::factory(),
            "status" => PartnershipStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => PartnershipStatus::Pending,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => PartnershipStatus::Active,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => PartnershipStatus::Suspended,
        ]);
    }
}
