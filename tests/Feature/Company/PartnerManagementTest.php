<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    public function testGuestCannotRequestPartnership(): void
    {
        $university = University::factory()->create();

        $this->post(route("company.universities.partnership.store", $university))
            ->assertRedirect(route("login"));
    }

    public function testNonCompanyRoleCannotRequestPartnership(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->create();

        $this->actingAs($user)
            ->post(route("company.universities.partnership.store", $university))
            ->assertForbidden();
    }

    public function testVerifiedCompanyAdminCanRequestPartnership(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $university = University::factory()->approved()->create();

        $this->actingAs($user)
            ->post(route("company.universities.partnership.store", $university))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
            "status" => PartnershipStatus::Pending->value,
            "requested_by" => PartnershipInitiator::Company->value,
        ]);
    }

    public function testRequestingSameUniversityTwiceIsRejected(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $university = University::factory()->approved()->create();
        Partnership::factory()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->post(route("company.universities.partnership.store", $university))
            ->assertInvalid("university");
    }

    public function testVerifiedCompanyAdminCanAcceptRequestFromUniversity(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $university = University::factory()->approved()->create();
        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->patch(route("company.universities.partnership.accept", $university))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
            "status" => PartnershipStatus::Active->value,
        ]);
    }

    public function testCompanyCannotAcceptItsOwnRequest(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $university = University::factory()->approved()->create();
        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->patch(route("company.universities.partnership.accept", $university))
            ->assertInvalid("university");
    }

    public function testVerifiedCompanyAdminCanRemovePartnership(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $university = University::factory()->approved()->create();
        Partnership::factory()->active()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->delete(route("company.universities.partnership.destroy", $university))
            ->assertRedirect();

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);
    }

    public function testCompanyAdminCannotRemovePartnershipBelongingToAnotherCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $otherCompany = Company::factory()->approved()->create();
        $university = University::factory()->approved()->create();
        Partnership::factory()->active()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->delete(route("company.universities.partnership.destroy", $university))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
    }
}
