<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOffersTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminOffersPageListsOffers(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        Offer::factory()->count(3)->create(["company_id" => $company->id]);

        $this->actingAs($admin)
            ->get("/admin/offers")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Offers")
                    ->has("offers.data", 3)
                    ->has("statuses"),
            );
    }

    public function testAdminOffersPageFiltersByStatus(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        Offer::factory()->create(["company_id" => $company->id, "status" => OfferStatus::Published]);
        Offer::factory()->create(["company_id" => $company->id, "status" => OfferStatus::Draft]);

        $this->actingAs($admin)
            ->get("/admin/offers?status=" . OfferStatus::Draft->value)
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Offers")
                    ->has("offers.data", 1)
                    ->where("offers.data.0.status", OfferStatus::Draft->value),
            );
    }

    public function testAdminOffersPageFiltersBySearch(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        Offer::factory()->create(["company_id" => $company->id, "title" => "Unikalna Oferta"]);
        Offer::factory()->create(["company_id" => $company->id, "title" => "Inna Praca"]);

        $this->actingAs($admin)
            ->get("/admin/offers?search=Unikalna")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Offers")
                    ->has("offers.data", 1)
                    ->where("offers.data.0.title", "Unikalna Oferta"),
            );
    }

    public function testGuestCannotAccessOffersPage(): void
    {
        $this->get("/admin/offers")->assertStatus(401);
    }

    public function testNonSuperAdminCannotAccessOffersPage(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);

        $this->actingAs($admin)->get("/admin/offers")->assertStatus(403);
    }

    public function testSuperAdminCanTakeDownPublishedOffer(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Published,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/offers/{$offer->id}/take-down")
            ->assertRedirect();

        $this->assertEquals(OfferStatus::Closed, $offer->fresh()->status);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $offer->id,
            "causer_id" => $admin->id,
            "description" => "offer_taken_down",
        ]);
    }

    public function testTakingDownOfferThatIsNotPublishedIsRejected(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Draft,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/offers/{$offer->id}/take-down")
            ->assertSessionHasErrors();

        $this->assertEquals(OfferStatus::Draft, $offer->fresh()->status);
    }

    public function testNonSuperAdminCannotTakeDownOffer(): void
    {
        $companyAdmin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Published,
        ]);

        $this->actingAs($companyAdmin)
            ->patch("/admin/offers/{$offer->id}/take-down")
            ->assertStatus(403);

        $this->assertEquals(OfferStatus::Published, $offer->fresh()->status);
    }
}
