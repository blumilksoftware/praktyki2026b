<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Admin;

use App\Actions\Admin\DeleteOrganizationAction;
use App\Enums\ApplicationStatus;
use App\Enums\InvitationStatus;
use App\Enums\OfferStatus;
use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteOrganizationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new DeleteOrganizationAction();
        $this->actingAs(User::factory()->create(["role" => UserRole::SuperAdmin]));
    }

    public function testItRevokesOnlyPendingInvitations(): void
    {
        $company = Company::factory()->approved()->create();

        $pending = OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company->value,
            "status" => InvitationStatus::Pending->value,
        ]);

        $accepted = OrganizationInvitation::factory()->create([
            "organization_id" => $company->id,
            "organization_type" => OrganizationType::Company->value,
            "status" => InvitationStatus::Accepted->value,
        ]);

        $this->action->execute($company);

        $this->assertSame(InvitationStatus::Revoked, $pending->fresh()->status);
        $this->assertSame(InvitationStatus::Accepted, $accepted->fresh()->status);
    }

    public function testItDoesNotRevokeInvitationsBelongingToAnotherOrganizationType(): void
    {
        $company = Company::factory()->approved()->create();
        $university = University::factory()->approved()->create([
            "id" => $company->id,
        ]);

        $universityInvitation = OrganizationInvitation::factory()->create([
            "organization_id" => $university->id,
            "organization_type" => OrganizationType::University->value,
            "status" => InvitationStatus::Pending->value,
        ]);

        $this->action->execute($company);

        $this->assertSame(InvitationStatus::Pending, $universityInvitation->fresh()->status);
    }

    public function testItClosesOffersAndRejectsPendingAndReviewedApplicationsForCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Published->value,
        ]);

        $pendingApplication = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending->value,
        ]);

        $reviewedApplication = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Reviewed->value,
        ]);

        $rejectedApplication = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Rejected->value,
        ]);

        $this->action->execute($company);

        $this->assertSame(OfferStatus::Closed, $offer->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $pendingApplication->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $reviewedApplication->fresh()->status);
        $this->assertSame(ApplicationStatus::Rejected, $rejectedApplication->fresh()->status);
    }

    public function testItHandlesCompanyWithoutOffersGracefully(): void
    {
        $company = Company::factory()->approved()->create();

        $this->action->execute($company);

        $this->assertSoftDeleted($company);
    }

    public function testItDeletesCompanyStaffUsers(): void
    {
        $company = Company::factory()->approved()->create();

        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->action->execute($company);

        $this->assertModelMissing($admin);
        $this->assertModelMissing($member);
    }

    public function testItDetachesStudentsButDeletesStaffForUniversity(): void
    {
        $university = University::factory()->approved()->create();

        $student = User::factory()->create([
            "role" => UserRole::Student,
            "organization_id" => $university->id,
        ]);

        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $this->action->execute($university);

        $this->assertNull($student->fresh()->organization_id);
        $this->assertModelMissing($admin);
    }

    public function testItDoesNotTouchUsersFromOtherOrganizations(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();

        $untouched = User::factory()->companyAdmin()->create(["organization_id" => $otherCompany->id]);

        $this->action->execute($company);

        $this->assertNotNull($untouched->fresh());
    }

    public function testItAnonymizesCompanyFieldsAndSoftDeletes(): void
    {
        $company = Company::factory()->approved()->create([
            "name" => "Acme Sp. z o.o.",
            "nip" => "1234567890",
        ]);

        $this->action->execute($company);

        $this->assertSoftDeleted($company);

        $anonymized = Company::withTrashed()->find($company->id);

        $this->assertNotNull($anonymized);
        $this->assertSame(sprintf("Deleted organization #%s", $company->id), $anonymized->name);
        $this->assertSame(sprintf("deleted-%s", $company->id), $anonymized->nip);
        $this->assertNull($anonymized->tags);
        $this->assertNull($anonymized->website);
        $this->assertNull($anonymized->description);
        $this->assertNull($anonymized->logo_path);
        $this->assertNull($anonymized->rejection_reason);
        $this->assertSame("", $anonymized->street);
        $this->assertSame("", $anonymized->postal_code);
        $this->assertSame("", $anonymized->city);
        $this->assertSame("", $anonymized->phone);
        $this->assertStringContainsString(sprintf("deleted-%s-", $company->id), $anonymized->email);
    }

    public function testItAnonymizesUniversityFields(): void
    {
        $university = University::factory()->approved()->create([
            "name" => "Politechnika Testowa",
            "domain" => "test.edu.pl",
        ]);

        $this->action->execute($university);

        $this->assertSoftDeleted($university);

        $anonymized = University::withTrashed()->find($university->id);

        $this->assertNotNull($anonymized);
        $this->assertSame(sprintf("Deleted organization #%s", $university->id), $anonymized->name);
        $this->assertSame(sprintf("deleted-%s.invalid", $university->id), $anonymized->domain);
        $this->assertNull($anonymized->external_form_url);
    }

    public function testItLogsActivityWithOriginalNameForCompany(): void
    {
        $company = Company::factory()->approved()->create(["name" => "Original Co"]);

        $this->action->execute($company);

        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $company->id,
            "subject_type" => $company->getMorphClass(),
            "description" => "company_deleted",
        ]);
    }

    public function testItLogsActivityWithOriginalNameForUniversity(): void
    {
        $university = University::factory()->approved()->create(["name" => "Original Uni"]);

        $this->action->execute($university);

        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $university->id,
            "subject_type" => $university->getMorphClass(),
            "description" => "university_deleted",
        ]);
    }
}
