<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\GetOffersSummary;
use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOffersSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsTitleStatusSpotsAndApplicationsCountForEachOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->closed()->create([
            "company_id" => $company->id,
            "title" => "Data Analyst Internship",
            "spots" => 5,
        ]);

        Application::factory()->count(3)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $action = new GetOffersSummary();
        $result = $action->execute($company);

        $this->assertCount(1, $result);
        $summary = $result->first();

        $this->assertSame($offer->id, $summary["id"]);
        $this->assertSame("Data Analyst Internship", $summary["title"]);
        $this->assertSame(OfferStatus::Closed->value, $summary["status"]);
        $this->assertSame(5, $summary["spots"]);
        $this->assertSame(2, $summary["remaining_spots"]);
        $this->assertSame(3, $summary["applications_count"]);
    }

    public function testPendingApplicationsDoNotConsumeSpots(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "spots" => 2,
        ]);

        Application::factory()->count(5)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $summary = (new GetOffersSummary())->execute($company)->first();

        $this->assertSame(2, $summary["remaining_spots"]);
        $this->assertSame(5, $summary["applications_count"]);
    }

    public function testRemainingSpotsNeverGoesBelowZero(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "spots" => 2,
        ]);

        Application::factory()->count(5)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $this->assertSame(0, (new GetOffersSummary())->execute($company)->first()["remaining_spots"]);
    }

    public function testItOnlyReturnsOffersBelongingToTheGivenCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();

        Offer::factory()->create(["company_id" => $company->id]);
        Offer::factory()->create(["company_id" => $otherCompany->id]);

        $action = new GetOffersSummary();
        $result = $action->execute($company);

        $this->assertCount(1, $result);
    }

    public function testItExcludesSoftDeletedOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $offer->delete();

        $action = new GetOffersSummary();
        $result = $action->execute($company);

        $this->assertCount(0, $result);
    }

    public function testItReturnsZeroApplicationsCountForOfferWithNoApplications(): void
    {
        $company = Company::factory()->approved()->create();
        Offer::factory()->create(["company_id" => $company->id]);

        $action = new GetOffersSummary();
        $result = $action->execute($company);

        $this->assertSame(0, $result->first()["applications_count"]);
    }
}
