<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);
        Storage::disk($disk)->put("cvs/test_cv.pdf", "CV PDF Content");

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

        $application = Application::where("offer_id", $offer->id)
            ->where("student_id", $user->id)
            ->first();

        $this->assertNotNull($application);
        $this->assertEquals("pending", $application->status->value);
        $this->assertNotNull($application->cv_path);
        $this->assertStringStartsWith("applications/cvs/", $application->cv_path);
        Storage::disk($disk)->assertExists($application->cv_path);
        $this->assertEquals("CV PDF Content", Storage::disk($disk)->get($application->cv_path));
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

        $this->actingAs($user)->post(route("student.offers.apply", $offer))->assertRedirect();

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
