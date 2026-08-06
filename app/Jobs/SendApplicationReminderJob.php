<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Mail\JobApplication\UnansweredApplicationReminderMail;
use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class SendApplicationReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
        public int $days,
    ) {}

    public function handle(): void
    {
        $this->application->refresh();

        if ($this->application->status !== ApplicationStatus::Pending) {
            return;
        }

        $offer = $this->application->offer;
        $company = $offer?->company;

        if ($offer === null || $company === null) {
            return;
        }

        $url = route('company.applications', [
            'offer' => $offer->id,
            'status' => 'pending',
        ]);

        Mail::to($company->email)->send(
            new UnansweredApplicationReminderMail(
                jobTitle: $offer->title,
                companyName: $company->name,
                daysPending: $this->days,
                applicationUrl: route('company.applications', [
                    'offer' => $offer->id,
                    'status' => 'pending',
                ]),
            ),
        );
    }
}
