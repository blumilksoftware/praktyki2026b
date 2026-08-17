<?php

declare(strict_types=1);

namespace Tests\Feature\University;

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

    public function testGuestCannotAddPartner(): void
    {
        $company = Company::factory()->create();

        $this->post(route("university.companies.partnership.store", $company))
            ->assertRedirect(route("login"));
    }

    public function testNonUniversityRoleCannotAddPartner(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->post(route("university.companies.partnership.store", $company))
            ->assertForbidden();
    }

    public function testVerifiedUniversityAdminCanAddPartner(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();

        $this->actingAs($user)
            ->post(route("university.companies.partnership.store", $company))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
            "status" => PartnershipStatus::Pending->value,
            "requested_by" => PartnershipInitiator::University->value,
        ]);
    }

    public function testVerifiedUniversityAdminCanAcceptRequestFromCompany(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->pending()->requestedByCompany()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->patch(route("university.companies.partnership.accept", $company))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
            "status" => PartnershipStatus::Active->value,
        ]);
    }

    public function testUniversityCannotAcceptItsOwnRequest(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->pending()->requestedByUniversity()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->patch(route("university.companies.partnership.accept", $company))
            ->assertInvalid("company");
    }

    public function testGuestCannotAcceptPartnership(): void
    {
        $company = Company::factory()->create();

        $this->patch(route("university.companies.partnership.accept", $company))
            ->assertRedirect(route("login"));
    }

    public function testAddingSameCompanyTwiceIsRejected(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->post(route("university.companies.partnership.store", $company))
            ->assertInvalid("company");
    }

    public function testVerifiedUniversityAdminCanRemovePartner(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $company = Company::factory()->approved()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->delete(route("university.companies.partnership.destroy", $company))
            ->assertRedirect();

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testNonUniversityRoleCannotRemovePartner(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->delete(route("university.companies.partnership.destroy", $company))
            ->assertForbidden();
    }

    public function testUniversityAdminCannotRemovePartnerBelongingToAnotherUniversity(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $otherUniversity = University::factory()->approved()->create();
        $company = Company::factory()->approved()->create();
        Partnership::factory()->create([
            "university_id" => $otherUniversity->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->delete(route("university.companies.partnership.destroy", $company))
            ->assertRedirect();

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $otherUniversity->id,
            "company_id" => $company->id,
        ]);
    }

    private function makeUniversityAdmin(University $university): User
    {
        return User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);
    }
}
