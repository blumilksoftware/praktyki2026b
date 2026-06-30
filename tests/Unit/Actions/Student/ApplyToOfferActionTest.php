<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplyToOfferActionTest extends TestCase
{
    use RefreshDatabase;

    private ApplyToOfferAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ApplyToOfferAction();
    }

    public function testStudentCanSuccessfullyApplyToActiveOfferWithSpots(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 3,
        ]);

        $application = $this->action->execute($student, $offer);

        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($offer->id, $application->offer_id);
        $this->assertEquals($student->id, $application->student_id);
        $this->assertEquals(ApplicationStatus::Pending, $application->status);

        $offer->refresh();
        $this->assertEquals(2, $offer->spots);
    }

    public function testApplyingWithoutUploadedCvIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => null,
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 3,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.student_no_cv"));

        try {
            $this->action->execute($student, $offer);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey("cv", $e->errors());

            throw $e;
        }
    }

    public function testApplyingToSameOfferTwiceIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 3,
        ]);

        $this->action->execute($student, $offer);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.already_applied"));

        try {
            $this->action->execute($student, $offer);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey("offer", $e->errors());

            throw $e;
        }
    }

    public function testApplyingToInactiveOfferIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => false,
            "spots" => 3,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_inactive"));

        try {
            $this->action->execute($student, $offer);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey("offer", $e->errors());

            throw $e;
        }
    }

    public function testApplyingToOfferWithNoSpotsIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "is_active" => true,
            "spots" => 0,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.no_spots_available"));

        try {
            $this->action->execute($student, $offer);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey("offer", $e->errors());

            throw $e;
        }
    }
}
