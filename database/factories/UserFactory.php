<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            "first_name" => fake()->firstName(),
            "last_name" => fake()->lastName(),
            "email" => fake()->unique()->safeEmail(),
            "email_verified_at" => now(),
            "password" => "password",
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "remember_token" => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes): array => [
            "email_verified_at" => null,
        ]);
    }

    public function pendingCompanyAdmin(): static
    {
        return $this->state(fn(array $attributes): array => [
            "first_name" => null,
            "last_name" => null,
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Pending,
        ]);
    }
}
