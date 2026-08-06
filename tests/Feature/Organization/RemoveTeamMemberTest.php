<?php

declare(strict_types=1);

namespace Tests\Feature\Organization;

use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotRemoveTeamMember(): void
    {
        $member = User::factory()->companyMember()->create(["organization_id" => Company::factory()]);

        $this->delete("/company/team/members/{$member->id}")
            ->assertRedirect("/login");

        $this->assertModelExists($member);
    }

    public function testCompanyAdminCanRemoveOwnCompanysMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$member->id}")
            ->assertRedirect();

        $this->assertModelMissing($member);
    }

    public function testUniversityAdminCanRemoveOwnUniversitysMember(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $member = User::factory()->universityMember()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->delete("/university/team/members/{$member->id}")
            ->assertRedirect();

        $this->assertModelMissing($member);
    }

    public function testAdminCannotRemoveThemselves(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$admin->id}")
            ->assertForbidden();

        $this->assertModelExists($admin);
    }

    public function testAdminCanRemoveAnotherAdminOfTheSameCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $coAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$coAdmin->id}")
            ->assertRedirect();

        $this->assertModelMissing($coAdmin);
    }

    public function testAdminCannotRemoveMemberOfAnotherCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $otherCompany->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$member->id}")
            ->assertForbidden();

        $this->assertModelExists($member);
    }

    public function testCompanyAdminCannotRemoveUniversityMemberSharingTheSameOrganizationId(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->universityMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$member->id}")
            ->assertForbidden();

        $this->assertModelExists($member);
    }

    public function testCompanyMemberCannotRemoveTeamMember(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);
        $colleague = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($member)
            ->delete("/company/team/members/{$colleague->id}")
            ->assertForbidden();

        $this->assertModelExists($colleague);
    }

    public function testUniversityMemberCannotRemoveTeamMember(): void
    {
        $university = University::factory()->approved()->create();
        $member = User::factory()->universityMember()->create(["organization_id" => $university->id]);
        $colleague = User::factory()->universityMember()->create(["organization_id" => $university->id]);

        $this->actingAs($member)
            ->delete("/university/team/members/{$colleague->id}")
            ->assertForbidden();

        $this->assertModelExists($colleague);
    }

    public function testAdminCannotRemoveStudent(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = User::factory()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$student->id}")
            ->assertForbidden();

        $this->assertModelExists($student);
    }

    public function testUnverifiedCompanyAdminCannotRemoveTeamMember(): void
    {
        $company = Company::factory()->pending()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/members/{$member->id}")
            ->assertRedirect("/company/verification/pending");

        $this->assertModelExists($member);
    }
}
