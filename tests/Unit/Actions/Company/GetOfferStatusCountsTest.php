<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\GetOfferStatusCounts;
use App\Models\Company;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOfferStatusCountsTest extends TestCase
{
    use RefreshDatabase;

    public function testItCountsOffersPerStatusForTheGivenCompany(): void
    {
        $company = Company::factory()->approved()->create();
        Offer::factory()->count(2)->published()->create(["company_id" => $company->id]);
        Offer::factory()->draft()->create(["company_id" => $company->id]);
        Offer::factory()->closed()->create(["company_id" => $company->id]);

        $action = new GetOfferStatusCounts();
        $counts = $action->execute($company);

        $this->assertSame(2, $counts["published"]);
        $this->assertSame(1, $counts["draft"]);
        $this->assertSame(1, $counts["closed"]);
        $this->assertArrayNotHasKey("expired", $counts);
    }

    public function testItOnlyCountsOffersBelongingToTheGivenCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();

        Offer::factory()->published()->create(["company_id" => $company->id]);
        Offer::factory()->published()->create(["company_id" => $otherCompany->id]);

        $action = new GetOfferStatusCounts();
        $counts = $action->execute($company);

        $this->assertSame(1, $counts["published"]);
    }

    public function testItExcludesSoftDeletedOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $offer->delete();

        $action = new GetOfferStatusCounts();
        $counts = $action->execute($company);

        $this->assertTrue($counts->isEmpty());
    }
}
