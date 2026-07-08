<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\StudyField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyField>
 */
class StudyFieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            "faculty_id" => Faculty::factory(),
            "name" => fake()->words(2, true),
        ];
    }
}
