<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanViewApprovedCompanyProfile(): void
    {
        $company = Company::factory()->approved()->create([
            "name" => "Acme Corp",
            "description" => "We build things.",
            "website" => "https://acme.test",
            "tags" => ["PHP", "Laravel"],
        ]);

        $offer = Offer::factory()->published()->create([
            "company_id" => $company->id,
            "title" => "Backend Internship",
            "spots" => 3,
        ]);

        $this->get(route("companies.show", $company))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/PublicProfile")
                    ->where("company.id", $company->id)
                    ->where("company.name", "Acme Corp")
                    ->where("company.description", "We build things.")
                    ->where("company.website", "https://acme.test")
                    ->where("company.tags", ["PHP", "Laravel"])
                    ->has("company.offers", 1)
                    ->where("company.offers.0.id", $offer->id)
                    ->where("company.offers.0.title", "Backend Internship")
                    ->where("company.offers.0.spots", 3),
            );
    }

    public function testProfileOnlyIncludesPublishedOffers(): void
    {
        $company = Company::factory()->approved()->create();

        Offer::factory()->published()->create(["company_id" => $company->id]);
        Offer::factory()->draft()->create(["company_id" => $company->id]);
        Offer::factory()->closed()->create(["company_id" => $company->id]);

        $this->get(route("companies.show", $company))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/PublicProfile")
                    ->has("company.offers", 1),
            );
    }

    public function testPendingCompanyProfileReturns404(): void
    {
        $company = Company::factory()->pending()->create();

        $this->get(route("companies.show", $company))->assertNotFound();
    }

    public function testRejectedCompanyProfileReturns404(): void
    {
        $company = Company::factory()->rejected()->create();

        $this->get(route("companies.show", $company))->assertNotFound();
    }

    public function testUnknownCompanyReturns404(): void
    {
        $this->get(route("companies.show", "nonexistent-id"))->assertNotFound();
    }
}
