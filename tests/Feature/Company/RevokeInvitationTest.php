<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\CompanyInvitationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotRevokeInvitation(): void
    {
        $invitation = CompanyInvitation::factory()->create();

        $this->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect("/login");
    }

    public function testCompanyAdminCanRevokeOwnCompanysPendingInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);
        $invitation = CompanyInvitation::factory()->create(["company_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect();

        $invitation->refresh();
        $this->assertEquals(CompanyInvitationStatus::Revoked, $invitation->status);
        $this->assertNotNull($invitation->revoked_at);
    }

    public function testCompanyAdminCannotRevokeAnotherCompanysInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);

        $otherCompany = Company::factory()->approved()->create();
        $invitation = CompanyInvitation::factory()->create(["company_id" => $otherCompany->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertForbidden();

        $this->assertEquals(CompanyInvitationStatus::Pending, $invitation->fresh()->status);
    }

    public function testCompanyMemberCannotRevokeInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);
        $invitation = CompanyInvitation::factory()->create(["company_id" => $company->id]);

        $this->actingAs($member)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertForbidden();
    }

    public function testRevokingAnAlreadyAcceptedInvitationFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);
        $invitation = CompanyInvitation::factory()->accepted()->create(["company_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect()
            ->assertSessionHasErrors("status");
    }

    public function testRevokingAnAlreadyRevokedInvitationFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);
        $invitation = CompanyInvitation::factory()->revoked()->create(["company_id" => $company->id]);

        $this->actingAs($admin)
            ->delete("/company/team/invitations/{$invitation->id}")
            ->assertRedirect()
            ->assertSessionHasErrors("status");
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
            "first_name" => null,
            "last_name" => null,
        ]);
    }
}
