<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\UpdateApplicationStatusAction;
use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Mail\JobApplication\JobApplicationStatusChangedMail;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateApplicationStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateApplicationStatusAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateApplicationStatusAction();
    }

    public function testItUpdatesApplicationStatusAndSendsEmailNotification(): void
    {
        Mail::fake();
        Notification::fake();

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $student = User::factory()->create();
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $updatedApplication = $this->action->execute($application, ApplicationStatus::Accepted);

        $this->assertEquals(ApplicationStatus::Accepted, $updatedApplication->status);
        $this->assertEquals(ApplicationStatus::Accepted, $application->fresh()->status);

        Mail::assertQueued(JobApplicationStatusChangedMail::class, fn(JobApplicationStatusChangedMail $mail) => $mail->hasTo($student->email) &&
                $mail->jobTitle === $offer->title &&
                $mail->companyName === $company->name &&
                $mail->status === __("emails.job_application.status.accepted"));

        Notification::assertSentTo(
            $student,
            ApplicationStatusChangedNotification::class,
            fn(ApplicationStatusChangedNotification $notification): bool => $notification->toArray($student)["application_id"] === $application->id &&
                $notification->toArray($student)["status"] === ApplicationStatus::Accepted->value,
        );
    }

    public function testAcceptingConsumesOneSpotWithoutChangingDeclaredCapacity(): void
    {
        Mail::fake();
        Notification::fake();

        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 3,
        ]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->action->execute($application, ApplicationStatus::Accepted);

        $offer->refresh();
        $this->assertEquals(3, $offer->spots);
        $this->assertEquals(2, $offer->remainingSpots());
        $this->assertEquals(OfferStatus::Published, $offer->status);
    }

    public function testRejectingFreesTheSpotBackUp(): void
    {
        Mail::fake();
        Notification::fake();

        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);
        $accepted = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $this->assertEquals(0, $offer->remainingSpots());

        $this->action->execute($accepted, ApplicationStatus::Rejected);

        $this->assertEquals(1, $offer->fresh()->remainingSpots());
    }

    public function testAcceptingIsRejectedWhenAllSpotsAreAlreadyTaken(): void
    {
        Mail::fake();
        Notification::fake();

        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);
        Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Accepted,
        ]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.offer_spots_exhausted"));

        try {
            $this->action->execute($application, ApplicationStatus::Accepted);
        } finally {
            $this->assertEquals(ApplicationStatus::Pending, $application->fresh()->status);
            Mail::assertNothingQueued();
        }
    }

    public function testAcceptingTheLastCandidateClosesTheOffer(): void
    {
        Mail::fake();
        Notification::fake();

        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->action->execute($application, ApplicationStatus::Accepted);

        $offer->refresh();
        $this->assertEquals(OfferStatus::Closed, $offer->status);
        $this->assertEquals(1, $offer->spots);
    }

    public function testAcceptedStudentIsNotNotifiedThatTheOfferBecameUnavailable(): void
    {
        Mail::fake();
        Notification::fake();

        $offer = Offer::factory()->create([
            "status" => OfferStatus::Published,
            "spots" => 1,
        ]);
        $acceptedStudent = User::factory()->create();
        $rejectedStudent = User::factory()->create();

        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $acceptedStudent->id,
            "status" => ApplicationStatus::Pending,
        ]);
        Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $rejectedStudent->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $this->action->execute($application, ApplicationStatus::Accepted);

        Mail::assertNotQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($acceptedStudent->email),
        );
        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($rejectedStudent->email),
        );
    }
}
