<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompanyInvitationStatus;
use App\Models\Company;
use App\Models\CompanyInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CompanyInvitation>
 */
class CompanyInvitationFactory extends Factory
{
    protected $model = CompanyInvitation::class;

    public function definition(): array
    {
        return [
            "company_id" => Company::factory(),
            "email" => fake()->unique()->safeEmail(),
            "token" => hash("sha256", Str::random(64)),
            "status" => CompanyInvitationStatus::Pending,
            "expires_at" => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes): array => [
            "expires_at" => now()->subDay(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => CompanyInvitationStatus::Accepted,
            "accepted_at" => now(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => CompanyInvitationStatus::Revoked,
            "revoked_at" => now(),
        ]);
    }
}
