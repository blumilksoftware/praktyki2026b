<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\ApplicationStatus;
use App\Jobs\SendApplicationReminderJob;
use App\Mail\JobApplication\UnansweredApplicationReminderMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class SendApplicationReminderJobTest extends TestCase
{
    use RefreshDatabase;

    public function testItSendsEmailToCompany(): void
    {
        Mail::fake();

        $company = Company::factory()->create(["email" => "company@example.com"]);
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $job = new SendApplicationReminderJob($application, 14);
        $job->handle();

        Mail::assertQueued(UnansweredApplicationReminderMail::class, fn($mail) => $mail->hasTo($company->email) &&
                $mail->daysPending === 14);
    }

    public function testItAbortsIfStatusChangedBeforeExecution(): void
    {
        Mail::fake();

        $application = Application::factory()->create(["status" => ApplicationStatus::Pending]);

        $job = new SendApplicationReminderJob($application, 14);

        $application->update(["status" => ApplicationStatus::Rejected]);

        $job->handle();

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function testItAbortsIfOfferOrCompanyIsMissing(): void
    {
        Mail::fake();
        $application = Mockery::mock(Application::class)->makePartial();
        $application->shouldReceive("refresh")->andReturnSelf();
        $application->shouldReceive("getAttribute")->with("status")->andReturn(ApplicationStatus::Pending);

        $application->shouldReceive("getAttribute")->with("offer")->andReturn(null);

        $job = new SendApplicationReminderJob($application, 14);
        $job->handle();

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }
}
