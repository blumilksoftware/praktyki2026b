<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\RemovePartnerAction;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemovePartnerActionTest extends TestCase
{
    use RefreshDatabase;

    private RemovePartnerAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RemovePartnerAction();
    }

    public function testCompanyCanCancelItsOwnPendingRequest(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);
    }

    public function testCompanyCanDeclineIncomingRequest(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);
    }

    public function testCompanyCanEndActivePartnership(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->active()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);
    }

    public function testRemovingNonExistentPartnershipIsANoop(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        $this->action->execute($company, $university);

        $this->assertDatabaseCount("partnerships", 0);
    }

    public function testRemovingDoesNotAffectPartnershipsOfOtherCompanies(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->active()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);
    }
}
