<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function testGuestCannotInviteTeamMember(): void
    {
        $this->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect("/login");
    }

    public function testCompanyAdminCanInviteTeamMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect();

        $this->assertDatabaseHas("company_invitations", [
            "company_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        Mail::assertQueued(TeamInvitationMail::class, fn(TeamInvitationMail $mail) => $mail->hasTo("invitee@example.com"));
    }

    public function testCompanyMemberCannotInviteTeamMember(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($member)
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertForbidden();

        $this->assertDatabaseMissing("company_invitations", [
            "company_id" => $company->id,
            "email" => "invitee@example.com",
        ]);
    }

    public function testInvitingAnEmailThatAlreadyBelongsToAnAccountFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = $this->makeCompanyAdmin($company);
        User::factory()->create(["email" => "existing@example.com"]);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "existing@example.com"])
            ->assertRedirect()
            ->assertSessionHasErrors("email");
    }

    public function testUnverifiedCompanyAdminCannotInviteTeamMember(): void
    {
        $company = Company::factory()->pending()->create();
        $admin = $this->makeCompanyAdmin($company);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect("/company/verification/pending");
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
