<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotChangeRole(): void
    {
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->patch("/admin/users/{$target->id}/role", ["role" => UserRole::CompanyAdmin->value])
            ->assertStatus(401);

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
    }

    public function testNonSuperAdminCannotChangeRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", ["role" => UserRole::CompanyAdmin->value])
            ->assertStatus(403);

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
    }

    public function testSuperAdminCanChangeAnotherUsersRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);
        $company = Company::factory()->approved()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", [
                "role" => UserRole::CompanyAdmin->value,
                "organization_id" => $company->id,
            ])
            ->assertRedirect();

        $this->assertEquals(UserRole::CompanyAdmin, $target->fresh()->role);
        $this->assertEquals($company->id, $target->fresh()->organization_id);
    }

    public function testSuperAdminCannotChangeTheirOwnRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}/role", ["role" => UserRole::Student->value])
            ->assertForbidden();

        $this->assertEquals(UserRole::SuperAdmin, $admin->fresh()->role);
    }

    public function testOrganizationRoleWithoutOrganizationIsRejected(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", ["role" => UserRole::CompanyAdmin->value])
            ->assertSessionHasErrors("organization_id");

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
        $this->assertNull($target->fresh()->organization_id);
    }

    public function testCompanyCannotBeAssignedToUniversityRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);
        $company = Company::factory()->approved()->create();

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", [
                "role" => UserRole::UniversityAdmin->value,
                "organization_id" => $company->id,
            ])
            ->assertSessionHasErrors("organization_id");

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
    }

    public function testDemotingToStudentClearsOrganization(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        $target = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "organization_id" => $company->id,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", ["role" => UserRole::Student->value])
            ->assertRedirect();

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
        $this->assertNull($target->fresh()->organization_id);
    }

    public function testPromotedUserCanReachTheirOrganizationPanel(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($admin)->patch("/admin/users/{$target->id}/role", [
            "role" => UserRole::CompanyAdmin->value,
            "organization_id" => $company->id,
        ]);

        $this->actingAs($target->fresh())->get("/company/dashboard")->assertOk();
    }

    public function testInvalidRoleValueIsRejected(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/role", ["role" => "not-a-role"])
            ->assertSessionHasErrors("role");

        $this->assertEquals(UserRole::Student, $target->fresh()->role);
    }
}
