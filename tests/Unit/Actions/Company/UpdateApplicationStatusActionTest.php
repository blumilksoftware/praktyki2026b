<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\UpdateApplicationStatusAction;
use App\Enums\ApplicationStatus;
use App\Mail\JobApplication\JobApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
    }
}
