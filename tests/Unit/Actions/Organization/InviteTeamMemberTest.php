<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Organization;

use App\Actions\Organization\InviteTeamMember;
use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    private InviteTeamMember $action;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->action = new InviteTeamMember();
    }

    public function testItCreatesCompanyInvitationAndQueuesMail(): void
    {
        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals($company->id, $invitation->organization_id);
        $this->assertEquals(OrganizationType::Company, $invitation->organization_type);
        $this->assertEquals("invitee@example.com", $invitation->email);
        $this->assertEquals($inviter->id, $invitation->invited_by);
        $this->assertEquals(InvitationStatus::Pending, $invitation->status);
        $this->assertTrue($invitation->expires_at->isFuture());

        Mail::assertQueued(
            TeamInvitationMail::class,
            fn(TeamInvitationMail $mail) => $mail->hasTo("invitee@example.com") &&
                $mail->organizationName === $company->name &&
                hash("sha256", $mail->token) === $invitation->token,
        );
    }

    public function testItCreatesUniversityInvitation(): void
    {
        $university = University::factory()->approved()->create();
        $inviter = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $invitation = $this->action->execute($university, $inviter, "invitee@example.com");

        $this->assertEquals($university->id, $invitation->organization_id);
        $this->assertEquals(OrganizationType::University, $invitation->organization_type);

        Mail::assertQueued(
            TeamInvitationMail::class,
            fn(TeamInvitationMail $mail) => $mail->organizationName === $university->name,
        );
    }

    public function testItRefreshesExistingPendingInvitation(): void
    {
        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $existing = OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals($existing->id, $invitation->id);
        $this->assertNotEquals($existing->token, $invitation->token);
        $this->assertDatabaseCount("organization_invitations", 1);
    }

    public function testItKeepsInvitationsOfDifferentOrganizationTypesApart(): void
    {
        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        OrganizationInvitation::factory()->forUniversity()->create([
            "organization_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertDatabaseCount("organization_invitations", 2);
    }

    public function testItRefreshesRevokedInvitationBackToPending(): void
    {
        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        OrganizationInvitation::factory()->revoked()->create([
            "organization_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals(InvitationStatus::Pending, $invitation->status);
        $this->assertNull($invitation->revoked_at);
    }
}
