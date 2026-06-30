<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyToOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotApplyToOffer(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->post(route("student.offers.apply", $offer));

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotApplyToOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);
        $offer = Offer::factory()->create();

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotApplyToOffer(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);
        $offer = Offer::factory()->create();

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertStatus(403);
    }

    public function testActiveStudentWithCvCanApplyToActiveOfferWithSpots(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 5,
        ]);

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertRedirect();
        $offer->refresh();
        $this->assertEquals(4, $offer->spots);
        $this->assertDatabaseHas("applications", [
            "offer_id" => $offer->id,
            "student_id" => $user->id,
            "status" => "pending",
        ]);
    }

    public function testApplyingWithoutUploadedCvIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => null,
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 5,
        ]);

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertInvalid("cv");
        $offer->refresh();
        $this->assertEquals(5, $offer->spots);
        $this->assertDatabaseMissing("applications", [
            "offer_id" => $offer->id,
            "student_id" => $user->id,
        ]);
    }

    public function testApplyingToSameOfferTwiceIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 5,
        ]);

        // First application
        $this->actingAs($user)->post(route("student.offers.apply", $offer))->assertRedirect();

        // Second application
        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertInvalid("offer");
        $offer->refresh();
        $this->assertEquals(4, $offer->spots); // Decremented only once
    }

    public function testApplyingToInactiveOfferIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => false,
            "spots" => 5,
        ]);

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertInvalid("offer");
        $offer->refresh();
        $this->assertEquals(5, $offer->spots);
        $this->assertDatabaseMissing("applications", [
            "offer_id" => $offer->id,
            "student_id" => $user->id,
        ]);
    }

    public function testApplyingToOfferWithNoSpotsIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 0,
        ]);

        $response = $this->actingAs($user)->post(route("student.offers.apply", $offer));

        $response->assertInvalid("offer");
        $offer->refresh();
        $this->assertEquals(0, $offer->spots);
        $this->assertDatabaseMissing("applications", [
            "offer_id" => $offer->id,
            "student_id" => $user->id,
        ]);
    }
}
