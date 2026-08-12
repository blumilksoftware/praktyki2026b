<?php

declare(strict_types=1);

namespace Tests\Feature\Organization;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotTransferOwnership(): void
    {
        $member = User::factory()->companyMember()->create(["organization_id" => Company::factory()]);

        $this->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertRedirect("/login");

        $this->assertEquals(UserRole::CompanyMember, $member->fresh()->role);
    }

    public function testCompanyAdminCanHandOverOwnershipToOwnMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertRedirect();

        $this->assertEquals(UserRole::CompanyMember, $admin->fresh()->role);
        $this->assertEquals(UserRole::CompanyAdmin, $member->fresh()->role);
    }

    public function testUniversityAdminCanHandOverOwnershipToOwnMember(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $member = User::factory()->universityMember()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->post("/university/team/members/{$member->id}/transfer-ownership")
            ->assertRedirect();

        $this->assertEquals(UserRole::UniversityMember, $admin->fresh()->role);
        $this->assertEquals(UserRole::UniversityAdmin, $member->fresh()->role);
    }

    public function testFormerAdminLosesTeamManagementAfterHandover(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertRedirect();

        $this->actingAs($admin->fresh())
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertForbidden();
    }

    public function testAdminCannotTransferOwnershipToThemselves(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$admin->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::CompanyAdmin, $admin->fresh()->role);
    }

    public function testAdminCannotTransferOwnershipToMemberOfAnotherCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $otherCompany->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::CompanyAdmin, $admin->fresh()->role);
        $this->assertEquals(UserRole::CompanyMember, $member->fresh()->role);
    }

    public function testCompanyAdminCannotTransferOwnershipToUniversityMemberSharingTheSameOrganizationId(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->universityMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::UniversityMember, $member->fresh()->role);
    }

    public function testAdminCannotTransferOwnershipToStudent(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = User::factory()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$student->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::Student, $student->fresh()->role);
    }

    public function testAdminCannotTransferOwnershipToInactiveMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::CompanyMember, $member->fresh()->role);
    }

    public function testCompanyMemberCannotTransferOwnership(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);
        $colleague = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($member)
            ->post("/company/team/members/{$colleague->id}/transfer-ownership")
            ->assertForbidden();

        $this->assertEquals(UserRole::CompanyMember, $colleague->fresh()->role);
    }

    public function testUnverifiedCompanyAdminCannotTransferOwnership(): void
    {
        $company = Company::factory()->pending()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/members/{$member->id}/transfer-ownership")
            ->assertRedirect("/company/verification/pending");

        $this->assertEquals(UserRole::CompanyMember, $member->fresh()->role);
    }
}
