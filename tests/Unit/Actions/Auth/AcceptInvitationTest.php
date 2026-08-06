<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AcceptInvitation;
use App\Enums\CompanyInvitationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\CompanyInvitation;
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
        $invitation = CompanyInvitation::factory()->create([
            "company_id" => $company->id,
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
        $this->assertEquals(CompanyInvitationStatus::Accepted, $invitation->status);
        $this->assertNotNull($invitation->accepted_at);
    }

    public function testItRejectsUnknownToken(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->execute("unknown-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsExpiredToken(): void
    {
        CompanyInvitation::factory()->expired()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsRevokedToken(): void
    {
        CompanyInvitation::factory()->revoked()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }

    public function testItRejectsAlreadyAcceptedToken(): void
    {
        CompanyInvitation::factory()->accepted()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute("plain-token", "Jan", "Kowalski", "Password123!");
    }
}
