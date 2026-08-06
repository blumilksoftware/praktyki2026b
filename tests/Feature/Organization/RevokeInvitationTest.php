<?php

declare(strict_types=1);

namespace Tests\Feature\Organization;

use App\Enums\InvitationStatus;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotRevokeInvitation(): void
    {
        $invitation = OrganizationInvitation::factory()->create();

        $this->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect("/login");
    }

    public function testCompanyAdminCanRevokeOwnCompanysPendingInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $invitation = OrganizationInvitation::factory()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect();

        $invitation->refresh();
        $this->assertEquals(InvitationStatus::Revoked, $invitation->status);
        $this->assertNotNull($invitation->revoked_at);
    }

    public function testUniversityAdminCanRevokeOwnUniversitysPendingInvitation(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $invitation = OrganizationInvitation::factory()->forUniversity()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->delete("/university/team/invitations/{$invitation->id}")
            ->assertRedirect();

        $invitation->refresh();
        $this->assertEquals(InvitationStatus::Revoked, $invitation->status);
        $this->assertNotNull($invitation->revoked_at);
    }

    public function testCompanyAdminCannotRevokeAnotherCompanysInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $invitation = OrganizationInvitation::factory()->create(["organization_id" => $otherCompany->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertForbidden();

        $this->assertEquals(InvitationStatus::Pending, $invitation->fresh()->status);
    }

    public function testCompanyAdminCannotRevokeUniversityInvitationSharingTheSameOrganizationId(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $invitation = OrganizationInvitation::factory()->forUniversity()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertForbidden();

        $this->assertEquals(InvitationStatus::Pending, $invitation->fresh()->status);
    }

    public function testCompanyMemberCannotRevokeInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);
        $invitation = OrganizationInvitation::factory()->create(["organization_id" => $company->id]);

        $this->actingAs($member)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertForbidden();
    }

    public function testUniversityMemberCannotRevokeInvitation(): void
    {
        $university = University::factory()->approved()->create();
        $member = User::factory()->universityMember()->create(["organization_id" => $university->id]);
        $invitation = OrganizationInvitation::factory()->forUniversity()->create(["organization_id" => $university->id]);

        $this->actingAs($member)
            ->delete("/university/team/invitations/{$invitation->id}")
            ->assertForbidden();
    }

    public function testRevokingAnAlreadyAcceptedInvitationFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $invitation = OrganizationInvitation::factory()->accepted()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect()
            ->assertSessionHasErrors("status");
    }

    public function testRevokingAnAlreadyRevokedInvitationFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $invitation = OrganizationInvitation::factory()->revoked()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect()
            ->assertSessionHasErrors("status");
    }
}
