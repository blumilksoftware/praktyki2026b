<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FavouriteOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotSaveOffer(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->post(route("student.offers.favourite.save", $offer));

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotSaveOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create();

        $response = $this->actingAs($user)->post(route("student.offers.favourite.save", $offer));

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotSaveOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);
        $offer = Offer::factory()->create();

        $response = $this->actingAs($user)->post(route("student.offers.favourite.save", $offer));

        $response->assertStatus(403);
    }

    public function testActiveStudentCanSavePublishedOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);

        $response = $this->actingAs($user)->post(route("student.offers.favourite.save", $offer));

        $response->assertRedirect();
        $this->assertDatabaseHas("student_favourites", [
            "student_id" => $user->id,
            "offer_id" => $offer->id,
        ]);
    }

    public function testSavingClosedOfferIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create(["status" => OfferStatus::Closed]);

        $response = $this->actingAs($user)->post(route("student.offers.favourite.save", $offer));

        $response->assertInvalid("offer");
        $this->assertDatabaseMissing("student_favourites", [
            "student_id" => $user->id,
            "offer_id" => $offer->id,
        ]);
    }

    public function testActiveStudentCanUnsaveOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);
        $user->favourites()->attach($offer->id);

        $response = $this->actingAs($user)->delete(route("student.offers.favourite.delete", $offer));

        $response->assertRedirect();
        $this->assertDatabaseMissing("student_favourites", [
            "student_id" => $user->id,
            "offer_id" => $offer->id,
        ]);
    }

    public function testActiveStudentCanUnsaveDeletedOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);
        $user->favourites()->attach($offer->id);
        $offer->delete();

        $response = $this->actingAs($user)->delete(route("student.offers.favourite.delete", $offer));

        $response->assertRedirect();
        $this->assertDatabaseMissing("student_favourites", [
            "student_id" => $user->id,
            "offer_id" => $offer->id,
        ]);
    }

    public function testGuestCannotViewFavouritesPage(): void
    {
        $response = $this->get(route("student.favourites"));

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotViewFavouritesPage(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->get(route("student.favourites"));

        $response->assertStatus(403);
    }

    public function testFavouritesPageListsSavedOffers(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create(["status" => OfferStatus::Published]);
        $user->favourites()->attach($offer->id);

        $response = $this->actingAs($user)->get(route("student.favourites"));

        $response->assertInertia(fn(Assert $page) => $page
            ->component("Student/Favourites")
            ->has("favourites", 1)
            ->where("favourites.0.id", $offer->id));
    }
}
