<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IndustryTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndustryTag>
 */
class IndustryTagFactory extends Factory
{
    public function definition(): array
    {
        return [
            "name" => fake()->unique()->word(),
        ];
    }
}
