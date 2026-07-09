<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faculty>
 */
class FacultyFactory extends Factory
{
    public function definition(): array
    {
        return [
            "university_id" => University::factory(),
            "name" => fake()->words(3, true),
        ];
    }
}
