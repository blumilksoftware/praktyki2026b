<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAddPartner(): void
    {
        $company = Company::factory()->create();

        $this->post(route("university.companies.partnership.store", $company))
            ->assertRedirect(route("login"));
    }

    public function testVerifiedUniversityAdminCanAddPartner(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();

        $this->actingAs($user)
            ->post(route("university.companies.partnership.store", $company))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testAddingSameCompanyTwiceIsRejected(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->post(route("university.companies.partnership.store", $company))
            ->assertInvalid("company");
    }

    public function testVerifiedUniversityAdminCanRemovePartner(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->delete(route("university.companies.partnership.destroy", $company))
            ->assertRedirect();

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
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
