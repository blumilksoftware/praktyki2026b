<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreReviewTest extends TestCase
{
    use RefreshDatabase;

    public function testStudentWithAcceptedApplicationCanReviewCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $student = User::factory()->create();

        Application::factory()->accepted()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
        ]);

        $this->actingAs($student)
            ->post(route("student.companies.reviews.store", $company), [
                "rating" => 5,
                "comment" => "Great internship!",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas("reviews", [
            "student_id" => $student->id,
            "company_id" => $company->id,
            "rating" => 5,
            "comment" => "Great internship!",
        ]);
    }

    public function testStudentWithoutAcceptedApplicationCannotReview(): void
    {
        $company = Company::factory()->approved()->create();
        $student = User::factory()->create();

        $this->actingAs($student)
            ->post(route("student.companies.reviews.store", $company), [
                "rating" => 4,
            ])
            ->assertSessionHasErrors("company");

        $this->assertDatabaseCount("reviews", 0);
    }

    public function testStudentCannotReviewSameCompanyTwice(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $student = User::factory()->create();

        Application::factory()->accepted()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
        ]);

        Review::factory()->create([
            "student_id" => $student->id,
            "company_id" => $company->id,
        ]);

        $this->actingAs($student)
            ->post(route("student.companies.reviews.store", $company), [
                "rating" => 3,
            ])
            ->assertSessionHasErrors("company");

        $this->assertDatabaseCount("reviews", 1);
    }

    public function testRatingMustBeBetweenOneAndFive(): void
    {
        $company = Company::factory()->approved()->create();
        $student = User::factory()->create();

        $this->actingAs($student)
            ->post(route("student.companies.reviews.store", $company), [
                "rating" => 6,
            ])
            ->assertSessionHasErrors("rating");
    }

    public function testGuestCannotReview(): void
    {
        $company = Company::factory()->approved()->create();

        $this->post(route("student.companies.reviews.store", $company), [
            "rating" => 5,
        ])->assertRedirect(route("login"));
    }

    public function testApplicationStatusOtherThanAcceptedCannotReview(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $student = User::factory()->create();

        Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->actingAs($student)
            ->post(route("student.companies.reviews.store", $company), [
                "rating" => 4,
            ])
            ->assertSessionHasErrors("company");
    }
}
