<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\PartnershipStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchCompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAccessCompanySearch(): void
    {
        $this->get(route("university.companies.index"))
            ->assertRedirect(route("login"));
    }

    public function testNonUniversityRoleCannotAccessCompanySearch(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route("university.companies.index"))
            ->assertForbidden();
    }

    public function testUnverifiedUniversityCannotAccessCompanySearch(): void
    {
        $university = University::factory()->pending()->create();
        $user = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->get(route("university.companies.index"))
            ->assertRedirect(route("university.verification.pending"));
    }

    public function testVerifiedUniversityAdminCanSearchCompanies(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $company1 = Company::factory()->approved()->create([
            "name" => "Company A",
            "city" => "Krakow",
            "tags" => ["IT", "Laravel"],
        ]);

        $company2 = Company::factory()->approved()->create([
            "name" => "Company B",
            "city" => "Warszawa",
            "tags" => ["Marketing"],
        ]);

        Partnership::factory()->create([
            "company_id" => $company1->id,
            "university_id" => $university->id,
            "status" => PartnershipStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route("university.companies.index", ["name" => "Company A"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/Companies/Index")
                    ->has("companies.data", 1)
                    ->where("companies.data.0.id", $company1->id)
                    ->where("companies.data.0.name", "Company A")
                    ->where("companies.data.0.partnership_status", "active")
                    ->where("filters.name", "Company A")
                    ->where("cityOptions", ["Krakow", "Warszawa"])
                    ->where("tagOptions", ["IT", "Laravel", "Marketing"]),
            );
    }

    public function testValidationErrorsForInvalidParams(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->get(route("university.companies.index", ["per_page" => 99]))
            ->assertRedirect()
            ->assertSessionHasErrors("per_page");
    }

    private function makeUniversityAdmin(University $university): User
    {
        return User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);
    }
}
