<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CreateOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotCreateOffer(): void
    {
        $response = $this->post("/company/offers", $this->validPayload());

        $response->assertRedirect("/login");
    }

    public function testPendingCompanyAdminCannotCreateOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->pendingCompanyAdmin()->create([
            "organization_id" => $company->id,
        ]);

        $response = $this->actingAs($user)->post("/company/offers", $this->validPayload());

        $response->assertForbidden();
    }

    public function testNonCompanyUserCannotCreateOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->post("/company/offers", $this->validPayload());

        $response->assertForbidden();
    }

    public function testUnverifiedCompanyCanCreateDraftOffer(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "status" => "draft",
        ]);

        $response->assertRedirect("/company/offers");
        $this->assertDatabaseHas("offers", [
            "company_id" => $company->id,
            "status" => "draft",
        ]);
    }

    public function testUnverifiedCompanyCannotPublishOffer(): void
    {
        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", $this->validPayload());

        $response->assertSessionHasErrors("status");
        $this->assertDatabaseMissing("offers", [
            "company_id" => $company->id,
        ]);
    }

    public function testVerifiedCompanyAdminCanCreateOfferWithRequiredFields(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", $this->validPayload());

        $response->assertRedirect("/company/offers");
        $this->assertDatabaseHas("offers", [
            "company_id" => $company->id,
            "title" => "Backend Developer Intern",
            "city" => "Warszawa",
            "work_mode" => "hybrid",
            "status" => "published",
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
        ]);

        $offer = Offer::where("company_id", $company->id)->firstOrFail();
        $this->assertEqualsWithDelta(52.2297, $offer->latitude, 0.0001);
        $this->assertEqualsWithDelta(21.0122, $offer->longitude, 0.0001);
    }

    public function testCreateFormExposesVerifiedCompanyStatus(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/offers/create");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->where("isCompanyVerified", true));
    }

    public function testCreateFormExposesPendingCompanyStatus(): void
    {
        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/offers/create");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->where("isCompanyVerified", false));
    }

    public function testCreateFormOnlyOffersVerifiedUniversitiesAsPreferredOptions(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $verified = University::factory()->approved()->create(["name" => "Verified University"]);
        University::factory()->pending()->create(["name" => "Pending University"]);

        $response = $this->actingAs($user)->get("/company/offers/create");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->has("universities", 1)
            ->where("universities.0.id", $verified->id));
    }

    public function testOfferIsCreatedUnderAuthenticatedCompanyRegardlessOfSubmittedCompanyId(): void
    {
        $this->fakeSuccessfulGeocoding();

        $ownCompany = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($ownCompany);

        $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "company_id" => $otherCompany->id,
        ]);

        $this->assertDatabaseHas("offers", [
            "title" => "Backend Developer Intern",
            "company_id" => $ownCompany->id,
        ]);
        $this->assertDatabaseMissing("offers", [
            "title" => "Backend Developer Intern",
            "company_id" => $otherCompany->id,
        ]);
    }

    public function testValidationFailsWhenRequiredFieldsAreMissing(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", []);

        $response->assertSessionHasErrors([
            "title",
            "description",
            "spots",
            "city",
            "start_date",
            "end_date",
            "work_mode",
            "status",
            "is_paid",
        ]);
    }

    public function testValidationFailsWhenSpotsIsLessThanOne(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "spots" => 0,
        ]);

        $response->assertSessionHasErrors("spots");
    }

    public function testValidationFailsWhenSpotsExceedsMaximum(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "spots" => 1001,
        ]);

        $response->assertSessionHasErrors("spots");
    }

    public function testValidationFailsWhenEndDateIsBeforeStartDate(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "start_date" => "2026-09-01",
            "end_date" => "2026-08-01",
        ]);

        $response->assertSessionHasErrors("end_date");
    }

    public function testValidationFailsWhenPaidOfferMissingSalaryRange(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "salary_min" => null,
            "salary_max" => null,
        ]);

        $response->assertSessionHasErrors(["salary_min", "salary_max"]);
    }

    public function testValidationFailsWhenSalaryMaxIsLessThanSalaryMin(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "salary_min" => 5000,
            "salary_max" => 3000,
        ]);

        $response->assertSessionHasErrors("salary_max");
    }

    public function testUnpaidOfferDoesNotRequireSalaryRange(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "is_paid" => false,
            "salary_min" => null,
            "salary_max" => null,
        ]);

        $response->assertRedirect("/company/offers");
        $this->assertDatabaseHas("offers", [
            "company_id" => $company->id,
            "is_paid" => false,
            "salary_min" => null,
            "salary_max" => null,
        ]);
    }

    public function testOfferCreationFailsWhenCityCannotBeGeocoded(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(["features" => []]),
        ]);

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->post("/company/offers", $this->validPayload());

        $response->assertSessionHasErrors("city");
        $this->assertDatabaseMissing("offers", [
            "company_id" => $company->id,
        ]);
    }

    public function testDraftOfferIsExcludedFromPublishedScopeButPublishedOneIsIncluded(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "title" => "Draft Offer",
            "status" => "draft",
        ]);

        $this->actingAs($user)->post("/company/offers", [
            ...$this->validPayload(),
            "title" => "Published Offer",
            "status" => "published",
        ]);

        $publishedTitles = Offer::published()->pluck("title");

        $this->assertNotContains("Draft Offer", $publishedTitles);
        $this->assertContains("Published Offer", $publishedTitles);
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

    private function validPayload(): array
    {
        return [
            "title" => "Backend Developer Intern",
            "description" => "Work on our backend systems.",
            "spots" => 3,
            "city" => "Warszawa",
            "start_date" => "2026-08-01",
            "end_date" => "2026-09-30",
            "work_mode" => "hybrid",
            "status" => "published",
            "is_paid" => true,
            "salary_min" => 3000,
            "salary_max" => 5000,
        ];
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
            "first_name" => null,
            "last_name" => null,
        ]);
    }
}
