<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            "name" => fake()->company(),
            "nip" => $this->generateValidNip(),
            "email" => fake()->unique()->companyEmail(),
            "street" => fake()->streetName(),
            "building_number" => fake()->buildingNumber(),
            "postal_code" => fake()->postcode(),
            "city" => fake()->city(),
            "phone" => fake()->phoneNumber(),
            "website" => fake()->optional()->url(),
            "logo_path" => null,
            "description" => fake()->optional()->paragraph(),
            "tags" => fake()->optional()->randomElements(["PHP", "Laravel", "Vue", "React", "IT", "Software"], 3),
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

    private function generateValidNip(): string
    {
        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];

        do {
            $digits = [];

            for ($i = 0; $i < 9; $i++) {
                $digits[] = random_int(0, 9);
            }

            $sum = 0;

            for ($i = 0; $i < 9; $i++) {
                $sum += $digits[$i] * $weights[$i];
            }

            $checksum = $sum % 11;
        } while ($checksum === 10);

        $digits[] = $checksum;

        return implode("", $digits);
    }
}
