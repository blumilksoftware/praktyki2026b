<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\UpdateOffer;
use App\DTO\Offer\UpdateOfferData;
use App\Enums\OfferStatus;
use App\Enums\WorkMode;
use App\Models\Company;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\University;
use App\Services\MapboxGeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesOfferFields(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->for($company)->create([
            "city" => "Warszawa",
        ]);

        $data = $this->dataFrom([
            "title" => "Updated Title",
            "spots" => 5,
            "city" => "Warszawa",
        ]);

        $action = new UpdateOffer(new MapboxGeocodingService());
        $updated = $action->execute($offer, $data);

        $this->assertEquals("Updated Title", $updated->title);
        $this->assertEquals(5, $updated->spots);
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "title" => "Updated Title",
            "spots" => 5,
        ]);
    }

    public function testItOnlyReGeocodesWhenCityChanges(): void
    {
        Http::fake();

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->for($company)->create([
            "city" => "Warszawa",
            "latitude" => 52.2297,
            "longitude" => 21.0122,
        ]);

        $data = $this->dataFrom([
            "title" => "Updated Title",
            "city" => "Warszawa",
        ]);

        $action = new UpdateOffer(new MapboxGeocodingService());
        $updated = $action->execute($offer, $data);

        Http::assertNothingSent();
        $this->assertEqualsWithDelta(52.2297, $updated->latitude, 0.0001);
        $this->assertEqualsWithDelta(21.0122, $updated->longitude, 0.0001);
    }

    public function testItReGeocodesWhenCityChanges(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->for($company)->create([
            "city" => "Warszawa",
        ]);

        $data = $this->dataFrom(["city" => "Kraków"]);

        $action = new UpdateOffer(new MapboxGeocodingService());
        $updated = $action->execute($offer, $data);

        $this->assertEquals("Kraków", $updated->city);
        $this->assertEqualsWithDelta(52.2297, $updated->latitude, 0.0001);
        $this->assertEqualsWithDelta(21.0122, $updated->longitude, 0.0001);
    }

    public function testItSyncsStudyFieldsAndUniversities(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->for($company)->create(["city" => "Warszawa"]);
        $studyField = StudyField::factory()->create();
        $university = University::factory()->approved()->create();

        $data = $this->dataFrom([
            "city" => "Warszawa",
            "study_field_ids" => [$studyField->id],
            "university_ids" => [$university->id],
        ]);

        $action = new UpdateOffer(new MapboxGeocodingService());
        $updated = $action->execute($offer, $data);

        $this->assertTrue($updated->studyFields->contains($studyField));
        $this->assertTrue($updated->universities->contains($university));
    }

    public function testItRejectsPublishingWhenCompanyIsNotVerified(): void
    {
        $company = Company::factory()->pending()->create();
        $offer = Offer::factory()->for($company)->draft()->create(["city" => "Warszawa"]);

        $data = $this->dataFrom([
            "city" => "Warszawa",
            "status" => OfferStatus::Published,
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
        ]);

        $action = new UpdateOffer(new MapboxGeocodingService());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_publish_requires_verification"));

        $action->execute($offer, $data);
    }

    public function testItRejectsOfferWhenCityCannotBeGeocoded(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(["features" => []]),
        ]);

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->for($company)->create(["city" => "Warszawa"]);

        $data = $this->dataFrom(["city" => "Nonexistentville"]);

        $action = new UpdateOffer(new MapboxGeocodingService());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.city_geocoding_failed"));

        $action->execute($offer, $data);
    }

    private function dataFrom(array $overrides): UpdateOfferData
    {
        return UpdateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::Hybrid,
            "status" => OfferStatus::Draft,
            "is_paid" => false,
            "study_field_ids" => [],
            "university_ids" => [],
            ...$overrides,
        ]);
    }

    private function fakeSuccessfulGeocoding(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response([
                "features" => [
                    ["center" => [21.0122, 52.2297]],
                ],
            ]),
        ]);
    }
}
