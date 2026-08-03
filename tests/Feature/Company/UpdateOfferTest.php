<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotViewEditForm(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->get("/company/offers/{$offer->id}/edit");

        $response->assertRedirect("/login");
    }

    public function testGuestCannotUpdateOffer(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->patch("/company/offers/{$offer->id}", $this->validPayload());

        $response->assertRedirect("/login");
    }

    public function testCompanyCannotEditAnotherCompanysOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $otherCompany = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $otherCompany->id]);
        $user = $this->makeCompanyAdmin($company);

        $editResponse = $this->actingAs($user)->get("/company/offers/{$offer->id}/edit");
        $updateResponse = $this->actingAs($user)->patch("/company/offers/{$offer->id}", $this->validPayload());

        $editResponse->assertForbidden();
        $updateResponse->assertForbidden();
    }

    public function testCompanyAdminCanViewEditFormPrefilledWithExistingValues(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "title" => "Backend Developer Intern",
        ]);
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/offers/{$offer->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn($page) => $page
            ->component("Company/EditOffer")
            ->where("offer.id", $offer->id)
            ->where("offer.title", "Backend Developer Intern"));
    }

    public function testCompanyAdminCanUpdateOwnOffer(): void
    {
        $this->fakeSuccessfulGeocoding();

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "city" => "Warszawa",
        ]);
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}", [
            ...$this->validPayload(),
            "title" => "Updated Offer Title",
        ]);

        $response->assertRedirect("/company/offers");
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "title" => "Updated Offer Title",
        ]);
    }

    public function testValidationFailsWhenEndDateIsBeforeStartDate(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id, "city" => "Warszawa"]);
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}", [
            ...$this->validPayload(),
            "start_date" => "2026-09-01",
            "end_date" => "2026-08-01",
        ]);

        $response->assertSessionHasErrors("end_date");
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
            "status" => "draft",
            "is_paid" => false,
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
