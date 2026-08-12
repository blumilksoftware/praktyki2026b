<?php

declare(strict_types=1);

namespace Tests\Feature\Offer;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanViewPublishedOfferDetails(): void
    {
        $offer = Offer::factory()->published()->create([
            "description" => "Build cool intern tools.",
        ]);

        $this->get(route("offers.show", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->where("isGuest", true)
                    ->where("canApply", false)
                    ->where("offer.id", $offer->id)
                    ->where("offer.description", "Build cool intern tools.")
                    ->where("offer.status", OfferStatus::Published->value),
            );
    }

    public function testStudentCanViewOfferWithDescriptionAndUniversities(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $company = Company::factory()->create([
            "verification_status" => VerificationStatus::Verified,
        ]);
        $university = University::factory()->create(["name" => "Test University"]);
        $offer = Offer::factory()->published()->create([
            "company_id" => $company->id,
            "description" => "Full internship description.",
            "title" => "Backend Intern",
        ]);
        $offer->universities()->sync([$university->id]);

        $this->actingAs($student)
            ->get(route("offers.show", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->where("isGuest", false)
                    ->where("canApply", true)
                    ->where("offer.title", "Backend Intern")
                    ->where("offer.description", "Full internship description.")
                    ->has("offer.preferred_universities", 1)
                    ->where("offer.preferred_universities.0.name", "Test University")
                    ->where("offer.company.is_verified", true)
                    ->has("offer.company.id"),
            );
    }

    public function testCompanyUserIsNeitherGuestNorAbleToApply(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
        $offer = Offer::factory()->published()->create();

        $this->actingAs($user)
            ->get(route("offers.show", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->where("isGuest", false)
                    ->where("canApply", false)
                    ->where("hasCv", false),
            );
    }

    public function testDraftOfferReturnsNotFound(): void
    {
        $offer = Offer::factory()->draft()->create();

        $this->get(route("offers.show", $offer))->assertNotFound();

        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("offers.show", $offer))
            ->assertNotFound();
    }

    public function testCompanyCanPreviewItsDraftOffer(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
        $offer = Offer::factory()->draft()->create([
            "company_id" => $company->id,
            "description" => "Draft preview description.",
        ]);

        $this->actingAs($user)
            ->get(route("offers.preview", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->where("isGuest", false)
                    ->where("canApply", false)
                    ->where("offer.id", $offer->id)
                    ->where("offer.description", "Draft preview description.")
                    ->where("offer.status", OfferStatus::Draft->value),
            );
    }

    public function testStudentCannotPreviewDraftOffer(): void
    {
        $company = Company::factory()->create();
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->draft()->create([
            "company_id" => $company->id,
        ]);

        $this->actingAs($student)
            ->get(route("offers.preview", $offer))
            ->assertForbidden();
    }

    public function testClosedOfferIsVisibleWithClosedStatus(): void
    {
        $offer = Offer::factory()->closed()->create();

        $this->get(route("offers.show", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->where("offer.status", OfferStatus::Closed->value),
            );
    }

    public function testShowIncludesSimilarOffersFromSameCity(): void
    {
        $offer = Offer::factory()->published()->create([
            "city" => "Wrocław",
            "title" => "Main Offer",
        ]);
        $similar = Offer::factory()->published()->create([
            "city" => "Wrocław",
            "title" => "Similar City Offer",
        ]);

        $this->get(route("offers.show", $offer))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers/Show")
                    ->has("similarOffers")
                    ->where(
                        "similarOffers",
                        function ($offers) use ($offer, $similar): bool {
                            $items = collect($offers);
                            $ids = $items->pluck("id");
                            $first = $items->first();

                            return $ids->contains($similar->id)
                                && !$ids->contains($offer->id)
                                && $items->count() <= 4
                                && (is_array($first) ? !array_key_exists("description", $first) : true);
                        },
                    ),
            );
    }
}
