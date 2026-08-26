<?php

declare(strict_types=1);

namespace Tests\Feature\Offer;

use App\Models\Offer;
use App\Models\StudyField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferFilterTest extends TestCase
{
    use RefreshDatabase;

    public function testStudyFieldFilterReturnsMatchingOffersOnListView(): void
    {
        $studyField = StudyField::factory()->create();
        $matchingOffer = Offer::factory()->published()->create();
        $matchingOffer->studyFields()->attach($studyField);
        Offer::factory()->published()->create();

        $this->get(route("offers.index", ["study_fields" => [$studyField->id]]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("offers.total", 1)
                    ->where("offers.data.0.id", $matchingOffer->id),
            );
    }

    public function testStudyFieldFilterReturnsMatchingOffersOnMapView(): void
    {
        $studyField = StudyField::factory()->create();
        $matchingOffer = Offer::factory()->published()->create();
        $matchingOffer->studyFields()->attach($studyField);
        Offer::factory()->published()->create();

        $response = $this->getJson(route("offers.map", ["study_fields" => [$studyField->id]]))
            ->assertOk();

        $response->assertJsonCount(1);
        $response->assertJsonFragment(["id" => $matchingOffer->id]);
    }

    public function testInvalidDateRangeIsIgnoredInsteadOfFailingOnListView(): void
    {
        Offer::factory()->published()->create();

        $this->get(route("offers.index", [
            "date_from" => "2026-06-01",
            "date_to" => "2026-01-01",
        ]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("offers.total", 1),
            );
    }

    public function testInvalidDateRangeIsIgnoredInsteadOfFailingOnMapView(): void
    {
        Offer::factory()->published()->create();

        $this->getJson(route("offers.map", [
            "date_from" => "2026-06-01",
            "date_to" => "2026-01-01",
        ]))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function testMalformedDateIsIgnoredInsteadOfFailing(): void
    {
        Offer::factory()->published()->create();

        $this->get(route("offers.index", ["date_from" => "not-a-date"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("offers.total", 1),
            );
    }
}
