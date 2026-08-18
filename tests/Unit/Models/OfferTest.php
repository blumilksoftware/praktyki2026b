<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\ApplicationStatus;
use App\Enums\WorkMode;
use App\Models\Application;
use App\Models\Offer;
use App\Models\StudyField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    public function testWithRemainingSpotsScopeExcludesOffersWhereAcceptedApplicationsFillAllSpots(): void
    {
        $available = Offer::factory()->published()->create(["spots" => 2]);
        Application::factory()->create([
            "offer_id" => $available->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $full = Offer::factory()->published()->create(["spots" => 1]);
        Application::factory()->create([
            "offer_id" => $full->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $results = Offer::withRemainingSpots()->get();

        $this->assertTrue($results->contains($available));
        $this->assertFalse($results->contains($full));
    }

    public function testWithRemainingSpotsScopeKeepsOffersWithOnlyPendingApplications(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 1]);
        Application::factory()->count(3)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->assertTrue(Offer::withRemainingSpots()->get()->contains($offer));
    }

    public function testRemainingSpotsCountsOnlyAcceptedApplications(): void
    {
        $offer = Offer::factory()->published()->create(["spots" => 3]);

        Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);
        Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Rejected,
        ]);
        Application::factory()->count(2)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->assertEquals(2, $offer->remainingSpots());
    }

    public function testForStudyFieldsScopeFiltersByAttachedStudyFields(): void
    {
        $matchingField = StudyField::factory()->create();
        $otherField = StudyField::factory()->create();

        $matching = Offer::factory()->published()->create();
        $matching->studyFields()->attach($matchingField);

        $nonMatching = Offer::factory()->published()->create();
        $nonMatching->studyFields()->attach($otherField);

        $results = Offer::forStudyFields([$matchingField->id])->get();

        $this->assertTrue($results->contains($matching));
        $this->assertFalse($results->contains($nonMatching));
    }

    public function testForStudyFieldsScopeReturnsAllOffersWhenEmpty(): void
    {
        $offer = Offer::factory()->published()->create();

        $results = Offer::forStudyFields([])->get();

        $this->assertTrue($results->contains($offer));
    }

    public function testForWorkModeScopeFiltersByWorkMode(): void
    {
        $remote = Offer::factory()->published()->create(["work_mode" => WorkMode::Remote]);
        $onSite = Offer::factory()->published()->create(["work_mode" => WorkMode::OnSite]);

        $results = Offer::forWorkMode(WorkMode::Remote)->get();

        $this->assertTrue($results->contains($remote));
        $this->assertFalse($results->contains($onSite));
    }

    public function testForWorkModeScopeReturnsAllOffersWhenNull(): void
    {
        $offer = Offer::factory()->published()->create();

        $results = Offer::forWorkMode(null)->get();

        $this->assertTrue($results->contains($offer));
    }

    public function testForCityScopeFiltersByExactCity(): void
    {
        $warsaw = Offer::factory()->published()->create(["city" => "Warszawa"]);
        $krakow = Offer::factory()->published()->create(["city" => "Kraków"]);

        $results = Offer::forCity("Warszawa")->get();

        $this->assertTrue($results->contains($warsaw));
        $this->assertFalse($results->contains($krakow));
    }

    public function testForDateRangeScopeMatchesOverlappingPeriods(): void
    {
        $overlapping = Offer::factory()->published()->create([
            "start_date" => "2026-08-01",
            "end_date" => "2026-08-31",
        ]);
        $outside = Offer::factory()->published()->create([
            "start_date" => "2026-10-01",
            "end_date" => "2026-10-31",
        ]);

        $results = Offer::forDateRange("2026-08-15", "2026-09-15")->get();

        $this->assertTrue($results->contains($overlapping));
        $this->assertFalse($results->contains($outside));
    }

    public function testForDateRangeScopeWidensWindowByFlexDays(): void
    {
        $justOutside = Offer::factory()->published()->create([
            "start_date" => "2026-09-20",
            "end_date" => "2026-10-10",
        ]);

        $withoutFlex = Offer::forDateRange("2026-08-01", "2026-09-15")->get();
        $withFlex = Offer::forDateRange("2026-08-01", "2026-09-15", 10)->get();

        $this->assertFalse($withoutFlex->contains($justOutside));
        $this->assertTrue($withFlex->contains($justOutside));
    }
}
