<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\ApplicationStatus;
use App\Mail\JobApplication\JobApplicationStatusChangedMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class UpdateApplicationStatusAction
{
    public function execute(Application $application, ApplicationStatus $status): Application
    {
        $application->update([
            "status" => $status,
        ]);

        $statusTranslated = __("emails.job_application.status.{$status->value}");

        Mail::to($application->student->email)->send(
            new JobApplicationStatusChangedMail(
                jobTitle: $application->offer->title,
                companyName: $application->offer->company->name,
                status: $statusTranslated,
                dashboardUrl: route("student.dashboard"),
            ),
        );

        return $application;
    }
}
