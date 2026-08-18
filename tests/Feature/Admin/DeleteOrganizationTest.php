<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotDeleteCompany(): void
    {
        $company = Company::factory()->create();

        $this->delete("/admin/companies/{$company->id}")->assertStatus(401);

        $this->assertNotSoftDeleted($company);
    }

    public function testNonSuperAdminCannotDeleteCompany(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $company = Company::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/companies/{$company->id}")
            ->assertStatus(403);

        $this->assertNotSoftDeleted($company);
    }

    public function testSuperAdminCanSoftDeleteCompany(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/companies/{$company->id}")
            ->assertRedirect();

        $this->assertSoftDeleted($company);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $company->id,
            "causer_id" => $admin->id,
            "description" => "company_deleted",
        ]);
    }

    public function testSuperAdminCanSoftDeleteUniversity(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $university = University::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/universities/{$university->id}")
            ->assertRedirect();

        $this->assertSoftDeleted($university);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $university->id,
            "causer_id" => $admin->id,
            "description" => "university_deleted",
        ]);
    }

    public function testDeletedCompanyDisappearsFromApplicationsList(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->pending()->create();

        $this->actingAs($admin)->delete("/admin/companies/{$company->id}");

        $this->assertEquals(0, Company::where("id", $company->id)->count());
        $this->assertEquals(1, Company::withTrashed()->where("id", $company->id)->count());
    }
}
