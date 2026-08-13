<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildCompanyProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsCompanyProfileFields(): void
    {
        $company = Company::factory()->approved()->create([
            "name" => "Acme Corp",
            "description" => "We build things.",
            "email" => "hello@acme.test",
            "phone" => "123456789",
            "website" => "https://acme.test",
            "street" => "Main Street 5",
            "postal_code" => "00-001",
            "city" => "Warsaw",
            "nip" => "1234563218",
            "tags" => ["PHP", "Laravel"],
        ]);

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertSame($company->id, $result["id"]);
        $this->assertSame("Acme Corp", $result["name"]);
        $this->assertSame("We build things.", $result["description"]);
        $this->assertSame("hello@acme.test", $result["email"]);
        $this->assertSame("123456789", $result["phone"]);
        $this->assertSame("https://acme.test", $result["website"]);
        $this->assertSame("Main Street 5", $result["street"]);
        $this->assertSame("00-001", $result["postalCode"]);
        $this->assertSame("Warsaw", $result["city"]);
        $this->assertSame("1234563218", $result["nip"]);
        $this->assertSame(["PHP", "Laravel"], $result["tags"]);
    }

    public function testItReturnsOnlyPublishedOffers(): void
    {
        $company = Company::factory()->approved()->create();

        $published = Offer::factory()->published()->create([
            "company_id" => $company->id,
            "title" => "Backend Internship",
        ]);
        Offer::factory()->draft()->create(["company_id" => $company->id]);
        Offer::factory()->closed()->create(["company_id" => $company->id]);

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertCount(1, $result["offers"]);
        $this->assertSame($published->id, $result["offers"]->first()["id"]);
        $this->assertSame("Backend Internship", $result["offers"]->first()["title"]);
    }

    public function testItExcludesOtherCompaniesOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();

        Offer::factory()->published()->create(["company_id" => $otherCompany->id]);

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertCount(0, $result["offers"]);
    }

    public function testItExcludesSoftDeletedOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $offer->delete();

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertCount(0, $result["offers"]);
    }

    public function testItReturnsOnlyActivePartnerUniversities(): void
    {
        $company = Company::factory()->approved()->create();

        $activePartner = University::factory()->approved()->create(["name" => "Active Uni"]);
        Partnership::factory()->active()->create([
            "company_id" => $company->id,
            "university_id" => $activePartner->id,
        ]);

        $pendingPartner = University::factory()->approved()->create(["name" => "Pending Uni"]);
        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $pendingPartner->id,
        ]);

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertCount(1, $result["partners"]);
        $this->assertSame("Active Uni", $result["partners"]->first()["name"]);
    }

    public function testItExcludesOtherCompaniesPartners(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();
        $university = University::factory()->approved()->create();

        Partnership::factory()->active()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $result = (new BuildCompanyProfileData())->execute($company);

        $this->assertCount(0, $result["partners"]);
    }
}
