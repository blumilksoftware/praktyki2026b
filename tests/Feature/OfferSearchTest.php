<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\WorkMode;
use App\Models\Offer;
use App\Models\StudyField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferSearchTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanSearchOffers(): void
    {
        $offer = Offer::factory()->published()->create();

        $this->get(route("offers.search"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Search")
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $offer->id)
                    ->has("mapPoints", 1)
                    ->where("mapPoints.0.id", $offer->id),
            );
    }

    public function testSearchExcludesDraftAndClosedOffers(): void
    {
        Offer::factory()->draft()->create();
        Offer::factory()->closed()->create();
        $published = Offer::factory()->published()->create();

        $this->get(route("offers.search"))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $published->id),
            );
    }

    public function testSearchExcludesOffersWithoutRemainingSpots(): void
    {
        Offer::factory()->published()->create(["spots" => 0]);
        $available = Offer::factory()->published()->create(["spots" => 1]);

        $this->get(route("offers.search"))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $available->id),
            );
    }

    public function testSearchFiltersByWorkMode(): void
    {
        $remote = Offer::factory()->published()->create(["work_mode" => WorkMode::Remote]);
        Offer::factory()->published()->create(["work_mode" => WorkMode::OnSite]);

        $this->get(route("offers.search", ["work_mode" => "remote"]))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $remote->id),
            );
    }

    public function testSearchFiltersByCity(): void
    {
        $warsaw = Offer::factory()->published()->create(["city" => "Warszawa"]);
        Offer::factory()->published()->create(["city" => "Kraków"]);

        $this->get(route("offers.search", ["city" => "Warszawa"]))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $warsaw->id),
            );
    }

    public function testSearchFiltersByStudyField(): void
    {
        $studyField = StudyField::factory()->create();
        $matching = Offer::factory()->published()->create();
        $matching->studyFields()->attach($studyField);
        Offer::factory()->published()->create();

        $this->get(route("offers.search", ["study_field_ids" => [$studyField->id]]))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $matching->id),
            );
    }

    public function testSearchFiltersByDateRangeWithFlexibility(): void
    {
        $withinFlex = Offer::factory()->published()->create([
            "start_date" => "2026-09-20",
            "end_date" => "2026-10-10",
        ]);

        $this->get(route("offers.search", [
            "date_from" => "2026-08-01",
            "date_to" => "2026-09-15",
            "date_flex_days" => 10,
        ]))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $withinFlex->id),
            );
    }

    public function testInvalidDateRangeIsRejected(): void
    {
        $this->get(route("offers.search", [
            "date_from" => "2026-09-15",
            "date_to" => "2026-08-01",
        ]))
            ->assertSessionHasErrors("date_to");
    }

    public function testSearchPageIncludesStudyFields(): void
    {
        $field = StudyField::factory()->create(["name" => "Computer Science"]);

        $this->get(route("offers.search"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Search")
                    ->has("studyFields", 1)
                    ->where("studyFields.0", [
                        "value" => $field->id,
                        "label" => "Computer Science",
                    ]),
            );
    }
}
