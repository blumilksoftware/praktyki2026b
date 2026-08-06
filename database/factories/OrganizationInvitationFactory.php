<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrganizationInvitation>
 */
class OrganizationInvitationFactory extends Factory
{
    protected $model = OrganizationInvitation::class;

    public function definition(): array
    {
        return [
            "organization_id" => Company::factory(),
            "organization_type" => OrganizationType::Company,
            "email" => fake()->unique()->safeEmail(),
            "token" => hash("sha256", Str::random(64)),
            "status" => InvitationStatus::Pending,
            "expires_at" => now()->addDays(7),
        ];
    }

    public function forUniversity(): static
    {
        return $this->state(fn(array $attributes): array => [
            "organization_id" => University::factory(),
            "organization_type" => OrganizationType::University,
        ]);
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
            "status" => InvitationStatus::Accepted,
            "accepted_at" => now(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn(array $attributes): array => [
            "status" => InvitationStatus::Revoked,
            "revoked_at" => now(),
        ]);
    }
}
