<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\SearchUniversities;
use App\DTO\Company\SearchUniversitiesData;
use App\Enums\PartnershipStatusFilter;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchUniversitiesTest extends TestCase
{
    use RefreshDatabase;

    private SearchUniversities $action;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SearchUniversities();
        $this->company = Company::factory()->approved()->create();
    }

    public function testItOnlyReturnsVerifiedUniversities(): void
    {
        University::factory()->approved()->create(["name" => "Verified Uni"]);
        University::factory()->pending()->create(["name" => "Pending Uni"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Verified Uni", $results->first()["name"]);
    }

    public function testItFiltersByNameCaseInsensitively(): void
    {
        University::factory()->approved()->create(["name" => "Alpha University"]);
        University::factory()->approved()->create(["name" => "Beta University"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: "alpha", city: null, partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Alpha University", $results->first()["name"]);
    }

    public function testItFiltersByCityCaseInsensitively(): void
    {
        University::factory()->approved()->create(["name" => "Uni A", "city" => "Legnica"]);
        University::factory()->approved()->create(["name" => "Uni B", "city" => "Warszawa"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: "legnica", partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Uni A", $results->first()["name"]);
    }

    public function testItFiltersByCityWithPolishDiacriticsRegardlessOfCasing(): void
    {
        if (DB::connection()->getDriverName() === "sqlite") {
            $this->markTestSkipped("SQLite LOWER() does not case-fold non-ASCII characters.");
        }

        University::factory()->approved()->create(["name" => "Uni A", "city" => "Świdnica"]);
        University::factory()->approved()->create(["name" => "Uni B", "city" => "Warszawa"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: "ŚWIDNICA", partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Uni A", $results->first()["name"]);
    }

    public function testItMarksRequestSentByCompanyAsOutgoing(): void
    {
        $university = University::factory()->approved()->create();

        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $this->company->id,
            "university_id" => $university->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertEquals("pending_outgoing", $results->first()["partnership_status"]);
    }

    public function testItMarksRequestSentByUniversityAsIncoming(): void
    {
        $university = University::factory()->approved()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $this->company->id,
            "university_id" => $university->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertEquals("pending_incoming", $results->first()["partnership_status"]);
    }

    public function testItReturnsActiveAndNoneStatuses(): void
    {
        $partnered = University::factory()->approved()->create(["name" => "Partnered Uni"]);
        University::factory()->approved()->create(["name" => "Unrelated Uni"]);

        Partnership::factory()->active()->create([
            "company_id" => $this->company->id,
            "university_id" => $partnered->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: null, perPage: 15), $this->company->id);
        $byName = collect($results->items())->keyBy("name");

        $this->assertEquals("active", $byName->get("Partnered Uni")["partnership_status"]);
        $this->assertEquals("none", $byName->get("Unrelated Uni")["partnership_status"]);
    }

    public function testItIgnoresPartnershipsOfOtherCompanies(): void
    {
        $otherCompany = Company::factory()->approved()->create();
        $university = University::factory()->approved()->create();

        Partnership::factory()->active()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: null, perPage: 15), $this->company->id);

        $this->assertEquals("none", $results->first()["partnership_status"]);
    }

    public function testItFiltersByActivePartnershipStatus(): void
    {
        $active = University::factory()->approved()->create(["name" => "Active Partner"]);
        $none = University::factory()->approved()->create(["name" => "No Partnership"]);

        Partnership::factory()->active()->create([
            "company_id" => $this->company->id,
            "university_id" => $active->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: PartnershipStatusFilter::Active, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Active Partner", $results->first()["name"]);
    }

    public function testItFiltersByPendingIncomingPartnershipStatus(): void
    {
        $incoming = University::factory()->approved()->create(["name" => "Incoming Partner"]);
        University::factory()->approved()->create(["name" => "Other Uni"]);

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $this->company->id,
            "university_id" => $incoming->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: PartnershipStatusFilter::PendingIncoming, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Incoming Partner", $results->first()["name"]);
    }

    public function testItFiltersByPendingOutgoingPartnershipStatus(): void
    {
        $outgoing = University::factory()->approved()->create(["name" => "Outgoing Partner"]);
        University::factory()->approved()->create(["name" => "Other Uni"]);

        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $this->company->id,
            "university_id" => $outgoing->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, partnershipStatus: PartnershipStatusFilter::PendingOutgoing, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Outgoing Partner", $results->first()["name"]);
    }
}
