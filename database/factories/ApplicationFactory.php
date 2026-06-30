<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            "offer_id" => Offer::factory(),
            "student_id" => User::factory(),
            "status" => ApplicationStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => ApplicationStatus::Pending,
        ]);
    }

    public function reviewed(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => ApplicationStatus::Reviewed,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => ApplicationStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => ApplicationStatus::Rejected,
        ]);
    }
}
