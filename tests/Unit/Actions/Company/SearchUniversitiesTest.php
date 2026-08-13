<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\SearchUniversities;
use App\DTO\Company\SearchUniversitiesData;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Verified Uni", $results->first()["name"]);
    }

    public function testItFiltersByNameCaseInsensitively(): void
    {
        University::factory()->approved()->create(["name" => "Alpha University"]);
        University::factory()->approved()->create(["name" => "Beta University"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: "alpha", city: null, perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Alpha University", $results->first()["name"]);
    }

    public function testItFiltersByCityCaseInsensitively(): void
    {
        University::factory()->approved()->create(["name" => "Uni A", "city" => "Legnica"]);
        University::factory()->approved()->create(["name" => "Uni B", "city" => "Warszawa"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: "legnica", perPage: 15), $this->company->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Uni A", $results->first()["name"]);
    }

    public function testItFiltersByCityWithPolishDiacriticsRegardlessOfCasing(): void
    {
        University::factory()->approved()->create(["name" => "Uni A", "city" => "Świdnica"]);
        University::factory()->approved()->create(["name" => "Uni B", "city" => "Warszawa"]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: "ŚWIDNICA", perPage: 15), $this->company->id);

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

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, perPage: 15), $this->company->id);

        $this->assertEquals("pending_outgoing", $results->first()["partnership_status"]);
    }

    public function testItMarksRequestSentByUniversityAsIncoming(): void
    {
        $university = University::factory()->approved()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $this->company->id,
            "university_id" => $university->id,
        ]);

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, perPage: 15), $this->company->id);

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

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, perPage: 15), $this->company->id);
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

        $results = $this->action->execute(new SearchUniversitiesData(name: null, city: null, perPage: 15), $this->company->id);

        $this->assertEquals("none", $results->first()["partnership_status"]);
    }
}
