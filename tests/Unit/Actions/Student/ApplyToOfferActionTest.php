<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
            "status" => OfferStatus::Published,
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

    public function testSuccessfulApplicationNotifiesCompanyAdmins(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $companyAdmin = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "organization_id" => $company->id,
        ]);
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "company_id" => $company->id,
            "status" => OfferStatus::Published,
            "spots" => 3,
        ]);

        $application = $this->action->execute($student, $offer);

        Notification::assertSentTo(
            $companyAdmin,
            NewApplicationNotification::class,
            fn(NewApplicationNotification $notification): bool => $notification->toArray($companyAdmin)["application_id"] === $application->id,
        );
    }

    public function testApplyingWithoutUploadedCvIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => null,
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 3,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.student_no_cv"));

        $this->action->execute($student, $offer);
    }

    public function testApplyingToSameOfferTwiceIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 3,
        ]);

        $this->action->execute($student, $offer);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.already_applied"));

        $this->action->execute($student, $offer);
    }

    public function testApplyingToInactiveOfferIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Closed,
            "spots" => 3,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_inactive"));

        $this->action->execute($student, $offer);
    }

    public function testApplyingToOfferWithNoSpotsIsRejected(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 0,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.no_spots_available"));

        $this->action->execute($student, $offer);
    }

    public function testStudentApplicationStoresCvSnapshot(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);
        Storage::disk($disk)->put("cvs/test_cv.pdf", "CV PDF Content");

        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 3,
        ]);

        $application = $this->action->execute($student, $offer);

        $this->assertNotNull($application->cv_path);
        $this->assertNotEquals("cvs/test_cv.pdf", $application->cv_path);
        $this->assertStringStartsWith("applications/cvs/", $application->cv_path);
        $this->assertStringEndsWith(".pdf", $application->cv_path);

        Storage::disk($disk)->assertExists($student->cv_path);
        Storage::disk($disk)->assertExists($application->cv_path);
        $this->assertEquals("CV PDF Content", Storage::disk($disk)->get($application->cv_path));
    }
}
