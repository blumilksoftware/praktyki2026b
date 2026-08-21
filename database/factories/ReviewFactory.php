<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            "student_id" => User::factory(),
            "company_id" => Company::factory(),
            "rating" => fake()->numberBetween(1, 5),
            "comment" => fake()->optional()->sentence(),
            "hidden" => false,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn(array $attributes): array => [
            "hidden" => true,
        ]);
    }
}
