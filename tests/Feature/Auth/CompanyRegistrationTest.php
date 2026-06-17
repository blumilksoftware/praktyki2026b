<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testCompanyCanRegisterWithValidData(): void
    {
        $this->postJson("/register/company", $this->validPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas("companies", [
            "nip" => "1234563218",
            "email" => "firma@example.com",
            "verification_status" => CompanyVerificationStatus::Pending->value,
        ]);

        $this->assertDatabaseHas("users", [
            "email" => "firma@example.com",
            "role" => UserRole::CompanyAdmin->value,
            "status" => UserStatus::Pending->value,
        ]);
    }

    public function testRegistrationFailsWithInvalidNipChecksum(): void
    {
        $this->postJson("/register/company", $this->validPayload(["nip" => "1234563219"]))
            ->assertStatus(422)
            ->assertJsonValidationErrors("nip");
    }

    public function testRegistrationFailsWithDuplicateNip(): void
    {
        Company::factory()->create(["nip" => "1234563218"]);

        $this->postJson("/register/company", $this->validPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors("nip");
    }

    public function testRegistrationFailsWithDuplicateEmail(): void
    {
        User::factory()->create(["email" => "firma@example.com"]);

        $this->postJson("/register/company", $this->validPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors("email");
    }

    public function testPendingCompanyAdminCannotAccessDashboard(): void
    {
        $company = Company::factory()->pending()->create();
        $user = User::factory()->pendingCompanyAdmin()->create([
            "organization_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->get("/company/dashboard")
            ->assertForbidden();
    }

    public function testApprovedCompanyAdminCanAccessDashboard(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        $this->actingAs($user)
            ->get("/company/dashboard")
            ->assertOk();
    }

    public function testPasswordIsNotStoredAsPlaintext(): void
    {
        $this->postJson("/register/company", $this->validPayload());

        $user = User::query()->firstWhere("email", "firma@example.com");

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check("Password123!", $user->password));
        $this->assertNotEquals("Password123!", $user->password);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            "company_name" => "Acme Sp. z o.o.",
            "nip" => "1234563218",
            "email" => "firma@example.com",
            "password" => "Password123!",
            "password_confirmation" => "Password123!",
            "street" => "ul. Kwiatowa",
            "building_number" => "1",
            "postal_code" => "00-001",
            "city" => "Warszawa",
            "phone" => "123456789",
            "terms" => true,
        ], $overrides);
    }
}
