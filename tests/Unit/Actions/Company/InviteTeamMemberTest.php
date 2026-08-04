<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\InviteTeamMember;
use App\Enums\CompanyInvitationStatus;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\CompanyInvitation;
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
        $this->action = new InviteTeamMember();
    }

    public function testItCreatesInvitationAndQueuesMail(): void
    {
        Mail::fake();

        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->create(["organization_id" => $company->id]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals($company->id, $invitation->company_id);
        $this->assertEquals("invitee@example.com", $invitation->email);
        $this->assertEquals($inviter->id, $invitation->invited_by);
        $this->assertEquals(CompanyInvitationStatus::Pending, $invitation->status);
        $this->assertTrue($invitation->expires_at->isFuture());

        Mail::assertQueued(
            TeamInvitationMail::class,
            fn(TeamInvitationMail $mail) => $mail->hasTo("invitee@example.com") &&
                $mail->companyName === $company->name &&
                hash("sha256", $mail->token) === $invitation->token,
        );
    }

    public function testItRefreshesExistingPendingInvitation(): void
    {
        Mail::fake();

        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->create(["organization_id" => $company->id]);

        $existing = CompanyInvitation::factory()->create([
            "company_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals($existing->id, $invitation->id);
        $this->assertNotEquals($existing->token, $invitation->token);
        $this->assertDatabaseCount("company_invitations", 1);
    }

    public function testItRefreshesRevokedInvitationBackToPending(): void
    {
        Mail::fake();

        $company = Company::factory()->approved()->create();
        $inviter = User::factory()->create(["organization_id" => $company->id]);

        CompanyInvitation::factory()->revoked()->create([
            "company_id" => $company->id,
            "email" => "invitee@example.com",
        ]);

        $invitation = $this->action->execute($company, $inviter, "invitee@example.com");

        $this->assertEquals(CompanyInvitationStatus::Pending, $invitation->status);
        $this->assertNull($invitation->revoked_at);
    }
}
