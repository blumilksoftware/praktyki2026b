<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\CreateOffer;
use App\DTO\Offer\CreateOfferData;
use App\Enums\OfferStatus;
use App\Enums\WorkMode;
use App\Models\Company;
use App\Models\StudyField;
use App\Models\University;
use App\Services\MapboxGeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesOfferUnderGivenCompanyWithGeocodedCoordinates(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();

        $data = CreateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::Hybrid,
            "status" => OfferStatus::Published,
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
            "study_field_ids" => [],
            "university_ids" => [],
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());
        $offer = $action->execute($company, $data);

        $this->assertEquals($company->id, $offer->company_id);
        $this->assertEquals("Backend Developer Intern", $offer->title);
        $this->assertEqualsWithDelta(52.2297, $offer->latitude, 0.0001);
        $this->assertEqualsWithDelta(21.0122, $offer->longitude, 0.0001);
        $this->assertEquals(OfferStatus::Published, $offer->status);
        $this->assertTrue($offer->is_paid);
        $this->assertEquals(3000, $offer->salary_min);
        $this->assertEquals(5000, $offer->salary_max);
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "company_id" => $company->id,
        ]);
    }

    public function testItCreatesUnpaidOfferWithoutSalaryRange(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();

        $data = CreateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::Hybrid,
            "status" => OfferStatus::Published,
            "is_paid" => false,
            "study_field_ids" => [],
            "university_ids" => [],
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());
        $offer = $action->execute($company, $data);

        $this->assertFalse($offer->is_paid);
        $this->assertNull($offer->salary_min);
        $this->assertNull($offer->salary_max);
    }

    public function testItAttachesStudyFieldsAndUniversities(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $studyField = StudyField::factory()->create();
        $university = University::factory()->approved()->create();

        $data = CreateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::Remote,
            "status" => OfferStatus::Draft,
            "is_paid" => false,
            "study_field_ids" => [$studyField->id],
            "university_ids" => [$university->id],
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());
        $offer = $action->execute($company, $data);

        $this->assertTrue($offer->studyFields->contains($studyField));
        $this->assertTrue($offer->universities->contains($university));
    }

    public function testItAllowsDraftOfferWhenCompanyIsNotVerified(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->pending()->create();

        $data = CreateOfferData::fromArray([
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
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());
        $offer = $action->execute($company, $data);

        $this->assertEquals(OfferStatus::Draft, $offer->status);
    }

    public function testItRejectsPublishedOfferWhenCompanyIsNotVerified(): void
    {
        Http::fake();

        $company = Company::factory()->pending()->create();

        $data = CreateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::Hybrid,
            "status" => OfferStatus::Published,
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
            "study_field_ids" => [],
            "university_ids" => [],
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_publish_requires_verification"));

        $action->execute($company, $data);
    }

    public function testItRejectsOfferWhenCityCannotBeGeocoded(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(["features" => []]),
        ]);

        $company = Company::factory()->approved()->create();

        $data = CreateOfferData::fromArray([
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Nonexistentville",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => WorkMode::OnSite,
            "status" => OfferStatus::Published,
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
            "study_field_ids" => [],
            "university_ids" => [],
        ]);

        $action = new CreateOffer(new MapboxGeocodingService());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.city_geocoding_failed"));

        $action->execute($company, $data);
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
