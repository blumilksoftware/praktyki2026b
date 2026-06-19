<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UniversityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UniversityRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testUniversityCanRegisterWithValidData(): void
    {
        $this->post("/register/university", $this->validPayload())
            ->assertRedirect(route("login"));

        $this->assertDatabaseHas("universities", [
            "email" => "uczelnia@example.com",
            "domain" => "example.com",
            "verification_status" => UniversityVerificationStatus::Pending->value,
        ]);

        $this->assertDatabaseHas("users", [
            "email" => "uczelnia@example.com",
            "role" => UserRole::UniversityAdmin->value,
            "status" => UserStatus::Pending->value,
        ]);
    }

    public function testRegistrationCreatesUserAndUniversityTogether(): void
    {
        $this->post("/register/university", $this->validPayload())
            ->assertRedirect(route("login"));

        $user = User::query()->firstWhere("email", "uczelnia@example.com");

        $this->assertNotNull($user);
        $this->assertNotNull($user->organization_id);
        $this->assertDatabaseHas("universities", ["id" => $user->organization_id]);
    }

    public function testRegistrationFailsWithDuplicateEmail(): void
    {
        User::factory()->create(["email" => "uczelnia@example.com"]);

        $this->post("/register/university", $this->validPayload())
            ->assertInvalid("email");
    }

    public function testRegistrationFailsWithMissingRequiredFields(): void
    {
        $this->post("/register/university", [])
            ->assertInvalid(["university_name", "email", "password", "address", "phone", "terms"]);
    }

    public function testRegistrationRequiresTermsAcceptance(): void
    {
        $this->post("/register/university", $this->validPayload(["terms" => false]))
            ->assertInvalid("terms");
    }

    public function testRegistrationRequiresPasswordConfirmation(): void
    {
        $this->post("/register/university", $this->validPayload(["password_confirmation" => "different"]))
            ->assertInvalid("password");
    }

    public function testPendingUniversityAdminCannotAccessDashboard(): void
    {
        $university = University::factory()->pending()->create();
        $user = User::factory()->pendingUniversityAdmin()->create([
            "organization_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->get("/university/dashboard")
            ->assertForbidden();
    }

    public function testApprovedUniversityAdminCanAccessDashboard(): void
    {
        $university = University::factory()->approved()->create();
        $user = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        $this->actingAs($user)
            ->get("/university/dashboard")
            ->assertOk();
    }

    public function testPasswordIsNotStoredAsPlaintext(): void
    {
        $this->post("/register/university", $this->validPayload());

        $user = User::query()->firstWhere("email", "uczelnia@example.com");

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check("Password123!", $user->password));
        $this->assertNotEquals("Password123!", $user->password);
    }

    public function testRegistrationFailsWithInvalidDomainFormat(): void
    {
        $this->post("/register/university", $this->validPayload(["domain" => "invalid_domain"]))
            ->assertInvalid("domain");
    }

    public function testRegistrationFailsWithDuplicateDomain(): void
    {
        University::factory()->create(["domain" => "example.com"]);

        $this->post("/register/university", $this->validPayload())
            ->assertInvalid("domain");
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            "university_name" => "Politechnika Przykładowa",
            "email" => "uczelnia@example.com",
            "domain" => "example.com",
            "password" => "Password123!",
            "password_confirmation" => "Password123!",
            "address" => "ul. Akademicka 1, 00-001 Warszawa",
            "phone" => "123456789",
            "terms" => true,
        ], $overrides);
    }
}
