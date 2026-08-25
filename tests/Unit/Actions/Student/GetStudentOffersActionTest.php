<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\GetStudentOffersAction;
use App\Enums\VerificationStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStudentOffersActionTest extends TestCase
{
    use RefreshDatabase;

    private GetStudentOffersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new GetStudentOffersAction();
    }

    public function testGuestSeesPublishedOffersWithoutFavoritesOrApplications(): void
    {
        $company = Company::factory()->create(["verification_status" => VerificationStatus::Verified]);
        Offer::factory()->published()->for($company)->create(["title" => "Backend Intern"]);
        Offer::factory()->draft()->for($company)->create(["title" => "Draft Offer"]);

        $result = $this->action->execute(null, [], 15);

        $this->assertCount(1, $result->items());
        $this->assertSame("Backend Intern", $result->items()[0]["title"]);
        $this->assertFalse($result->items()[0]["has_applied"]);
        $this->assertFalse($result->items()[0]["is_favorite"]);
        $this->assertNull($result->items()[0]["applied_at"]);
    }

    public function testSearchMatchesTitleCityOrCompanyName(): void
    {
        $company = Company::factory()->create(["name" => "Blumilk"]);
        $byTitle = Offer::factory()->published()->for($company)->create(["title" => "Laravel Developer", "city" => "Wrocław"]);
        $byCity = Offer::factory()->published()->for($company)->create(["title" => "Frontend Developer", "city" => "Warszawa"]);
        $byCompany = Offer::factory()->published()->for(
            Company::factory()->create(["name" => "Warszawa Software House"]),
        )->create(["title" => "QA Engineer", "city" => "Kraków"]);
        Offer::factory()->published()->for($company)->create(["title" => "Unrelated", "city" => "Gdańsk"]);

        $result = $this->action->execute(null, ["search" => "Warszawa"], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertContains($byCity->id, $ids);
        $this->assertContains($byCompany->id, $ids);
        $this->assertNotContains($byTitle->id, $ids);
    }

    public function testCitiesFilterAppliesWhenNoRadiusFilterIsActive(): void
    {
        $company = Company::factory()->create();
        $wroclaw = Offer::factory()->published()->for($company)->create(["city" => "Wrocław"]);
        Offer::factory()->published()->for($company)->create(["city" => "Poznań"]);

        $result = $this->action->execute(null, ["cities" => ["Wrocław"]], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertSame([$wroclaw->id], $ids);
    }

    public function testCitiesFilterIsIgnoredWhenRadiusFilterIsActive(): void
    {
        $company = Company::factory()->create();
        $inRadiusWrongCity = Offer::factory()->published()->for($company)->create([
            "city" => "Poznań",
            "latitude" => 51.0803,
            "longitude" => 17.0210,
        ]);

        $result = $this->action->execute(null, [
            "cities" => ["Wrocław"],
            "latitude" => 51.1079,
            "longitude" => 17.0385,
            "radius_km" => 10,
        ], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertContains($inRadiusWrongCity->id, $ids);
    }

    public function testWorkModesFilter(): void
    {
        $company = Company::factory()->create();
        $remote = Offer::factory()->published()->for($company)->create(["work_mode" => "remote"]);
        Offer::factory()->published()->for($company)->create(["work_mode" => "onSite"]);

        $result = $this->action->execute(null, ["work_modes" => ["remote"]], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertSame([$remote->id], $ids);
    }

    public function testDateRangeFilter(): void
    {
        $company = Company::factory()->create();
        $inRange = Offer::factory()->published()->for($company)->create(["start_date" => "2026-09-01"]);
        Offer::factory()->published()->for($company)->create(["start_date" => "2026-12-01"]);

        $result = $this->action->execute(null, [
            "date_from" => "2026-08-01",
            "date_to" => "2026-10-01",
        ], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertSame([$inRange->id], $ids);
    }

    public function testStudyFieldsFilter(): void
    {
        $company = Company::factory()->create();
        $it = StudyField::factory()->create(["name" => "Computer Science"]);
        $law = StudyField::factory()->create(["name" => "Law"]);

        $matching = Offer::factory()->published()->for($company)->create();
        $matching->studyFields()->attach($it);

        $other = Offer::factory()->published()->for($company)->create();
        $other->studyFields()->attach($law);

        $result = $this->action->execute(null, ["study_fields" => [$it->id]], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertSame([$matching->id], $ids);
    }

    public function testRadiusFilterReturnsOnlyOffersWithinRadiusOrderedByDistance(): void
    {
        $company = Company::factory()->create();

        $near = Offer::factory()->published()->for($company)->create([
            "latitude" => 51.1090,
            "longitude" => 17.0300,
        ]);

        $mid = Offer::factory()->published()->for($company)->create([
            "latitude" => 51.4000,
            "longitude" => 17.2000,
        ]);

        Offer::factory()->published()->for($company)->create([
            "latitude" => 52.2297,
            "longitude" => 21.0122,
        ]);

        $result = $this->action->execute(null, [
            "latitude" => 51.1079,
            "longitude" => 17.0385,
            "radius_km" => 50,
        ], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertSame([$near->id, $mid->id], $ids);
        $this->assertNotNull($result->items()[0]["distance_km"]);
    }

    public function testRadiusFilterIsIgnoredWhenOnlySomeCoordinateFiltersAreProvided(): void
    {
        $company = Company::factory()->create();
        $offer = Offer::factory()->published()->for($company)->create([
            "latitude" => 52.2297,
            "longitude" => 21.0122,
        ]);

        $result = $this->action->execute(null, [
            "latitude" => 51.1079,
            "longitude" => 17.0385,
        ], 15);

        $ids = collect($result->items())->pluck("id")->all();
        $this->assertContains($offer->id, $ids);
        $this->assertNull($result->items()[0]["distance_km"]);
    }

    public function testAuthenticatedStudentSeesOwnApplicationAndFavoriteStatus(): void
    {
        $student = User::factory()->create();
        $company = Company::factory()->create();
        $applied = Offer::factory()->published()->for($company)->create();
        $favorited = Offer::factory()->published()->for($company)->create();
        Offer::factory()->published()->for($company)->create();

        Application::factory()->for($applied)->create(["student_id" => $student->id]);
        $student->favourites()->attach($favorited->id);

        $result = $this->action->execute($student, [], 15);

        $items = collect($result->items())->keyBy("id");
        $this->assertTrue($items[$applied->id]["has_applied"]);
        $this->assertNotNull($items[$applied->id]["applied_at"]);
        $this->assertTrue($items[$favorited->id]["is_favorite"]);
        $this->assertFalse($items[$applied->id]["is_favorite"]);
    }

    public function testRemainingSpotsNeverGoesNegative(): void
    {
        $company = Company::factory()->create();
        $offer = Offer::factory()->published()->for($company)->create(["spots" => 1]);

        Application::factory()->count(3)->for($offer)->accepted()->create();

        $result = $this->action->execute(null, [], 15);

        $this->assertSame(0, $result->items()[0]["remaining_spots"]);
    }

    public function testCompanyIsVerifiedFlagReflectsVerificationStatus(): void
    {
        $verified = Company::factory()->create(["verification_status" => VerificationStatus::Verified]);
        $pending = Company::factory()->create(["verification_status" => VerificationStatus::Pending]);

        Offer::factory()->published()->for($verified)->create();
        Offer::factory()->published()->for($pending)->create();

        $result = $this->action->execute(null, [], 15);

        $items = collect($result->items())->keyBy(fn(array $item) => $item["company"]["id"]);
        $this->assertTrue($items[$verified->id]["company"]["is_verified"]);
        $this->assertFalse($items[$pending->id]["company"]["is_verified"]);
    }
}
