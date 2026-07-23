<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\SearchCompanies;
use App\DTO\University\SearchCompaniesData;
use App\Enums\OfferStatus;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchCompaniesTest extends TestCase
{
    use RefreshDatabase;

    private SearchCompanies $action;
    private University $university;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SearchCompanies();
        $this->university = University::factory()->approved()->create();
    }

    public function testItOnlyReturnsVerifiedCompanies(): void
    {
        Company::factory()->approved()->create(["name" => "Verified Co"]);
        Company::factory()->pending()->create(["name" => "Pending Co"]);
        Company::factory()->rejected()->create(["name" => "Rejected Co"]);

        $data = new SearchCompaniesData(name: null, city: null, tag: null, perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Verified Co", $results->first()["name"]);
    }

    public function testItFiltersByNameCaseInsensitively(): void
    {
        Company::factory()->approved()->create(["name" => "Alpha Team"]);
        Company::factory()->approved()->create(["name" => "Beta Team"]);

        $data = new SearchCompaniesData(name: "alpha", city: null, tag: null, perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Alpha Team", $results->first()["name"]);
    }

    public function testItFiltersByCityCaseInsensitively(): void
    {
        Company::factory()->approved()->create(["name" => "Company A", "city" => "Krakow"]);
        Company::factory()->approved()->create(["name" => "Company B", "city" => "Warszawa"]);

        $data = new SearchCompaniesData(name: null, city: "krakow", tag: null, perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Company A", $results->first()["name"]);
    }

    public function testItFiltersByTag(): void
    {
        Company::factory()->approved()->create(["name" => "Company A", "tags" => ["Laravel", "Vue"]]);
        Company::factory()->approved()->create(["name" => "Company B", "tags" => ["React", "Node"]]);

        $data = new SearchCompaniesData(name: null, city: null, tag: "vue", perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Company A", $results->first()["name"]);
    }

    public function testItFiltersByTagRegardlessOfInternalCasing(): void
    {
        Company::factory()->approved()->create(["name" => "Company A", "tags" => ["iOS", "Swift"]]);
        Company::factory()->approved()->create(["name" => "Company B", "tags" => ["Android", "Kotlin"]]);

        $data = new SearchCompaniesData(name: null, city: null, tag: "ios", perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertCount(1, $results);
        $this->assertEquals("Company A", $results->first()["name"]);
    }

    public function testItReturnsActiveOffersCount(): void
    {
        $company = Company::factory()->approved()->create();

        Offer::factory()->count(2)->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Published,
        ]);

        Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Draft,
        ]);

        $data = new SearchCompaniesData(name: null, city: null, tag: null, perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $this->assertEquals(2, $results->first()["active_offers_count"]);
    }

    public function testItReturnsCurrentPartnershipStatus(): void
    {
        $company1 = Company::factory()->approved()->create(["name" => "Partnered Co"]);
        Partnership::factory()->create([
            "company_id" => $company1->id,
            "university_id" => $this->university->id,
            "status" => PartnershipStatus::Active,
        ]);

        $company2 = Company::factory()->approved()->create(["name" => "Non-Partnered Co"]);

        $data = new SearchCompaniesData(name: null, city: null, tag: null, perPage: 15);
        $results = $this->action->execute($data, $this->university->id);

        $resultsMap = collect($results->items())->keyBy("name");

        $this->assertEquals("active", $resultsMap->get("Partnered Co")["partnership_status"]);
        $this->assertEquals("none", $resultsMap->get("Non-Partnered Co")["partnership_status"]);
    }
}
