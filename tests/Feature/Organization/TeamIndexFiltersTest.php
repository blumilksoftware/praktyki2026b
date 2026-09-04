<?php

declare(strict_types=1);

namespace Tests\Feature\Organization;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TeamIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyStaffRolesAreListedAsMembersNotStudents(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        User::factory()->create([
            "organization_id" => $company->id,
            "role" => UserRole::Student,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("members.data", 1)
                    ->where("members.data.0.role", "companyAdmin"),
            );
    }

    public function testMembersOfAnotherOrganizationAreNotListed(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $otherCompany = Company::factory()->approved()->create();
        User::factory()->companyMember()->create([
            "organization_id" => $otherCompany->id,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->has("members.data", 1));
    }

    public function testOnlyPendingInvitationsAreListed(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Pending,
            "email" => "pending@example.com",
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Accepted,
            "email" => "accepted@example.com",
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Revoked,
            "email" => "revoked@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("invitations.data", 1)
                    ->where("invitations.data.0.email", "pending@example.com"),
            );
    }

    public function testInvitationsFromAnotherOrganizationAreNotListed(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $otherCompany = Company::factory()->approved()->create();
        OrganizationInvitation::factory()->create([
            "organization_id" => $otherCompany->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->has("invitations.data", 0));
    }

    public function testCompanyAdminDoesNotSeeUniversityInvitationsForSameOrganizationId(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::University,
            "status" => InvitationStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->has("invitations.data", 0));
    }

    public function testVerifiedUniversityAdminCanOpenTeamPage(): void
    {
        $university = University::factory()->approved()->create([
            "name" => "State University",
        ]);

        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "status" => UserStatus::Active,
        ]);

        User::factory()->universityMember()->create([
            "organization_id" => $university->id,
            "email" => "member@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->where("organization.name", "State University")
                    ->where("organization.type", "university")
                    ->has("members.data", 2),
            );
    }

    public function testSearchMatchesLastNameAndFullName(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
            "first_name" => "Carol",
            "last_name" => "Owner",
            "email" => "carol.owner@example.com",
        ]);

        $member = User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "first_name" => "Alice",
            "last_name" => "Smith",
            "email" => "asmith@example.com",
        ]);

        User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "first_name" => "Bob",
            "last_name" => "Jones",
            "email" => "bjones@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", ["member_search" => "smith"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("members.data", 1)
                    ->where("members.data.0.email", $member->email),
            );

        $this->actingAs($admin)
            ->get(route("team.index", ["member_search" => "alice smith"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("members.data", 1)
                    ->where("members.data.0.email", $member->email),
            );
    }

    public function testInvitationSearchIgnoresCase(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Pending,
            "email" => "Gwendolyn@example.com",
        ]);

        OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Pending,
            "email" => "marlow@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", ["invitation_search" => "GWENDOLYN"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("invitations.data", 1)
                    ->where("invitations.data.0.email", "Gwendolyn@example.com"),
            );
    }

    public function testGenericSearchParamFallsBackToMemberSearch(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $member = User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "email" => "findme@example.com",
        ]);

        User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "email" => "nomatch@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", ["search" => "findme"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("members.data", 1)
                    ->where("members.data.0.email", $member->email),
            );
    }

    public function testMembersAndInvitationsPaginateIndependently(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        User::factory()->companyMember()->count(3)->create([
            "organization_id" => $company->id,
        ]);

        OrganizationInvitation::factory()->count(3)->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company,
            "status" => InvitationStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", [
                "per_page" => 2,
                "member_page" => 2,
                "invitation_page" => 2,
            ]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("members.data", 2)
                    ->has("invitations.data", 1)
                    ->where("filters.member_page", 2)
                    ->where("filters.invitation_page", 2),
            );
    }

    public function testPerPageDefaultsToTenWhenNotProvided(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where("filters.per_page", 10));
    }

    public function testMemberPageBelowOneIsRejected(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", ["member_page" => 0]))
            ->assertRedirect()
            ->assertSessionHasErrors("member_page");
    }

    public function testInvitationPageBelowOneIsRejected(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index", ["invitation_page" => 0]))
            ->assertRedirect()
            ->assertSessionHasErrors("invitation_page");
    }

    public function testStudentCannotOpenTeamPage(): void
    {
        $student = User::factory()->create([
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("team.index"))
            ->assertForbidden();
    }

    public function testInactiveCompanyAdminCannotOpenTeamPage(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route("team.index"))
            ->assertForbidden();
    }
}
