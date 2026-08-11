<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchUniversitiesTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAccessUniversitySearch(): void
    {
        $this->get(route("company.universities.index"))
            ->assertRedirect(route("login"));
    }

    public function testNonCompanyRoleCannotAccessUniversitySearch(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route("company.universities.index"))
            ->assertForbidden();
    }

    public function testVerifiedCompanyAdminCanSearchUniversities(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $university1 = University::factory()->approved()->create([
            "name" => "University A",
            "city" => "Legnica",
        ]);

        University::factory()->approved()->create([
            "name" => "University B",
            "city" => "Warszawa",
        ]);

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university1->id,
        ]);

        $this->actingAs($user)
            ->get(route("company.universities.index", ["name" => "University A"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Universities/Index")
                    ->has("universities.data", 1)
                    ->where("universities.data.0.id", $university1->id)
                    ->where("universities.data.0.name", "University A")
                    ->where("universities.data.0.partnership_status", "pending_incoming")
                    ->where("filters.name", "University A")
                    ->where("cityOptions", ["Legnica", "Warszawa"]),
            );
    }

    public function testValidationErrorsForInvalidParams(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $this->actingAs($user)
            ->get(route("company.universities.index", ["per_page" => 99]))
            ->assertRedirect()
            ->assertSessionHasErrors("per_page");
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
    }
}
