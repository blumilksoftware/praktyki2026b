<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AcceptInvitation;
use App\Enums\InvitationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    private AcceptInvitation $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AcceptInvitation();
    }

    public function testItCreatesCompanyMemberAndMarksInvitationAccepted(): void
    {
        $company = Company::factory()->approved()->create();
        $invitation = OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "email" => "invitee@example.com",
            "token" => hash("sha256", "plain-token"),
        ]);

        $user = $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");

        $this->assertEquals("Jan", $user->first_name);
        $this->assertEquals("Kowalski", $user->last_name);
        $this->assertEquals("invitee@example.com", $user->email);
        $this->assertEquals(UserRole::CompanyMember, $user->role);
        $this->assertEquals(UserStatus::Active, $user->status);
        $this->assertEquals($company->id, $user->organization_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check("Password123!", $user->password));

        $invitation->refresh();
        $this->assertEquals(InvitationStatus::Accepted, $invitation->status);
        $this->assertNotNull($invitation->accepted_at);
    }

    public function testItCreatesUniversityMemberForUniversityInvitation(): void
    {
        $university = University::factory()->approved()->create();
        OrganizationInvitation::factory()->forUniversity()->create([
            "organization_id" => $university->id,
            "email" => "invitee@example.com",
            "token" => hash("sha256", "plain-token"),
        ]);

        $user = $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");

        $this->assertEquals(UserRole::UniversityMember, $user->role);
        $this->assertEquals($university->id, $user->organization_id);
    }

    public function testItRevokesOtherPendingInvitationsForTheSameEmail(): void
    {
        OrganizationInvitation::factory()->create([
            "email" => "invitee@example.com",
            "token" => hash("sha256", "plain-token"),
        ]);

        $otherOrganization = OrganizationInvitation::factory()->forUniversity()->create([
            "email" => "invitee@example.com",
        ]);
        $otherEmail = OrganizationInvitation::factory()->create([
            "email" => "someone-else@example.com",
        ]);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");

        $otherOrganization->refresh();
        $this->assertEquals(InvitationStatus::Revoked, $otherOrganization->status);
        $this->assertNotNull($otherOrganization->revoked_at);

        $this->assertEquals(InvitationStatus::Pending, $otherEmail->fresh()->status);
    }

    public function testItRejectsUnknownToken(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->execute("unknown-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsExpiredToken(): void
    {
        OrganizationInvitation::factory()->expired()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsRevokedToken(): void
    {
        OrganizationInvitation::factory()->revoked()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsAlreadyAcceptedToken(): void
    {
        OrganizationInvitation::factory()->accepted()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }
}
