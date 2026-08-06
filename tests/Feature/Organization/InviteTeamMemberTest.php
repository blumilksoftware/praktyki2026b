<?php

declare(strict_types=1);

namespace Tests\Feature\Organization;

use App\Enums\OrganizationType;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\University;
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

        $this->post("/university/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect("/login");
    }

    public function testCompanyAdminCanInviteTeamMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect();

        $this->assertDatabaseHas("organization_invitations", [
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company->value,
            "email" => "invitee@example.com",
        ]);

        Mail::assertQueued(TeamInvitationMail::class, fn(TeamInvitationMail $mail) => $mail->hasTo("invitee@example.com"));
    }

    public function testUniversityAdminCanInviteTeamMember(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->post("/university/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect();

        $this->assertDatabaseHas("organization_invitations", [
            "organization_id" => $university->id,
            "organization_type" => OrganizationType::University->value,
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

        $this->assertDatabaseMissing("organization_invitations", [
            "organization_id" => $company->id,
            "email" => "invitee@example.com",
        ]);
    }

    public function testUniversityMemberCannotInviteTeamMember(): void
    {
        $university = University::factory()->approved()->create();
        $member = User::factory()->universityMember()->create(["organization_id" => $university->id]);

        $this->actingAs($member)
            ->post("/university/team/invitations", ["email" => "invitee@example.com"])
            ->assertForbidden();

        $this->assertDatabaseMissing("organization_invitations", [
            "organization_id" => $university->id,
            "email" => "invitee@example.com",
        ]);
    }

    public function testInvitingAnEmailThatAlreadyBelongsToAnAccountFails(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        User::factory()->create(["email" => "existing@example.com"]);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "existing@example.com"])
            ->assertRedirect()
            ->assertSessionHasErrors("email");
    }

    public function testUnverifiedCompanyAdminCannotInviteTeamMember(): void
    {
        $company = Company::factory()->pending()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->post("/company/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect("/company/verification/pending");
    }

    public function testUnverifiedUniversityAdminCannotInviteTeamMember(): void
    {
        $university = University::factory()->pending()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->post("/university/team/invitations", ["email" => "invitee@example.com"])
            ->assertRedirect("/university/verification/pending");
    }
}
