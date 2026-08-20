<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Mail\JobApplication\NewJobApplicationMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function testApplyingDoesNotChangeDeclaredCapacity(): void
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
        $this->assertEquals(3, $offer->spots);
        $this->assertEquals(3, $offer->remainingSpots());
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

    public function testSuccessfulApplicationMailsEveryCompanyUser(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $companyUsers = User::factory()->count(2)->create([
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

        $this->action->execute($student, $offer);

        foreach ($companyUsers as $companyUser) {
            Mail::assertQueued(
                NewJobApplicationMail::class,
                fn(NewJobApplicationMail $mail): bool => $mail->hasTo($companyUser->email)
                    && $mail->studentName === $student->fullName()
                    && $mail->jobTitle === $offer->title,
            );
        }
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

    public function testApplyingIsRejectedWhenAcceptedApplicationsFillAllSpots(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);

        Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.no_spots_available"));

        $this->action->execute($student, $offer);
    }

    public function testPendingApplicationsDoNotBlockFurtherApplications(): void
    {
        $student = User::factory()->create([
            "cv_path" => "cvs/test_cv.pdf",
        ]);
        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);

        Application::factory()->count(3)->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $application = $this->action->execute($student, $offer);

        $this->assertEquals(ApplicationStatus::Pending, $application->status);
        $this->assertEquals(1, $offer->fresh()->remainingSpots());
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
