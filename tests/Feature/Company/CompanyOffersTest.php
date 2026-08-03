<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyOffersTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAccessOffersList(): void
    {
        $this->get(route("company.offers"))->assertRedirect(route("login"));
    }

    public function testNonCompanyAdminRoleCannotAccessOffersList(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)->get(route("company.offers"))->assertStatus(403);
    }

    public function testUnverifiedCompanyCanAccessOffersListToSeeItsDrafts(): void
    {
        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => "draft",
        ]);

        $this->actingAs($user)
            ->get(route("company.offers"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Offers")
                    ->has("offers", 1)
                    ->where("offers.0.id", $offer->id)
                    ->where("isCompanyVerified", false),
            );
    }

    public function testPendingUserAccountCannotAccessOffersList(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->pendingCompanyAdmin()->create([
            "organization_id" => $company->id,
        ]);

        $this->actingAs($user)->get(route("company.offers"))->assertForbidden();
    }

    public function testCompanyAdminCanAccessOffersListWithSummaryData(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->published()->create([
            "company_id" => $company->id,
            "title" => "Backend Internship",
            "spots" => 3,
        ]);

        Application::factory()->count(2)->create(["offer_id" => $offer->id]);

        $this->actingAs($user)
            ->get(route("company.offers"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Offers")
                    ->has("offers.data", 1)
                    ->where("offers.data.0.id", $offer->id)
                    ->where("offers.data.0.title", "Backend Internship")
                    ->where("offers.data.0.status", "published")
                    ->where("offers.data.0.spots", 3)
                    ->where("offers.data.0.applications_count", 2)
                    ->where("isCompanyVerified", true),
            );
    }

    public function testOffersListOnlyIncludesOwnCompanysOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $otherCompany = Company::factory()->approved()->create();
        Offer::factory()->create(["company_id" => $otherCompany->id]);

        $this->actingAs($user)
            ->get(route("company.offers"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Offers")
                    ->has("offers.data", 0),
            );
    }

    public function testOffersListExcludesSoftDeletedOffers(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $offer->delete();

        $this->actingAs($user)
            ->get(route("company.offers"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Offers")
                    ->has("offers.data", 0),
            );
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
    }
}
