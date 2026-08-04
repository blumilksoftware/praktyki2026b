<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\CompanyInvitationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function testInvitationPageRenders(): void
    {
        $this->get("/invitations/some-token")
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component("Auth/AcceptInvitation")
                ->where("token", "some-token"));
    }

    public function testAcceptingInvitationLogsUserInAndRedirectsToDashboard(): void
    {
        $company = Company::factory()->approved()->create();
        $invitation = CompanyInvitation::factory()->create([
            "company_id" => $company->id,
            "email" => "invitee@example.com",
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->post("/invitations/plain-token", $this->validPayload())
            ->assertRedirect(route("company.dashboard"));

        $this->assertAuthenticated();
        $this->assertDatabaseHas("users", [
            "email" => "invitee@example.com",
            "role" => UserRole::CompanyMember->value,
            "organization_id" => $company->id,
        ]);

        $invitation->refresh();
        $this->assertEquals(CompanyInvitationStatus::Accepted, $invitation->status);
    }

    public function testAcceptingWithUnknownTokenFails(): void
    {
        $this->post("/invitations/garbage-token", $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasErrors("token");

        $this->assertGuest();
    }

    public function testAcceptingWithExpiredTokenFails(): void
    {
        CompanyInvitation::factory()->expired()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->post("/invitations/plain-token", $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasErrors("token");

        $this->assertGuest();
    }

    public function testAcceptingWithRevokedTokenFails(): void
    {
        CompanyInvitation::factory()->revoked()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->post("/invitations/plain-token", $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasErrors("token");

        $this->assertGuest();
    }

    public function testReSubmittingAnAlreadyAcceptedTokenFails(): void
    {
        CompanyInvitation::factory()->accepted()->create([
            "token" => hash("sha256", "plain-token"),
        ]);

        $this->post("/invitations/plain-token", $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasErrors("token");

        $this->assertGuest();
    }

    public function testAcceptingWhenEmailAlreadyRegisteredFails(): void
    {
        $company = Company::factory()->approved()->create();
        $invitation = CompanyInvitation::factory()->create([
            "company_id" => $company->id,
            "email" => "invitee@example.com",
            "token" => hash("sha256", "plain-token"),
        ]);
        User::factory()->create(["email" => "invitee@example.com"]);

        $this->post("/invitations/plain-token", $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasErrors("email");

        $this->assertGuest();

        $invitation->refresh();
        $this->assertEquals(CompanyInvitationStatus::Pending, $invitation->status);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "password" => "Password123!",
            "password_confirmation" => "Password123!",
            "terms" => true,
        ], $overrides);
    }
}
