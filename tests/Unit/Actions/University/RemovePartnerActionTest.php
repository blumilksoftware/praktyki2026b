<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\RemovePartnerAction;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
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

    public function testUniversityCanRemovePartnership(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testRemovingNonExistentPartnershipIsANoop(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testRemovingPartnershipDoesNotAffectExistingApplications(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("applications", [
            "id" => $application->id,
        ]);
    }
}
