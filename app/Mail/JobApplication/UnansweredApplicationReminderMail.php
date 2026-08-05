<?php

declare(strict_types=1);

namespace App\Mail\JobApplication;

use App\Mail\QueueableMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UnansweredApplicationReminderMail extends QueueableMailable
{
    public function __construct(
        public readonly string $jobTitle,
        public readonly string $companyName,
        public readonly int $daysPending,
        public readonly string $applicationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.job_application.reminder_subject", [
                "job_title" => $this->jobTitle,
                "days" => $this->daysPending,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.job_application.unanswered_application_reminder",
            with: [
                "jobTitle" => $this->jobTitle,
                "companyName" => $this->companyName,
                "daysPending" => $this->daysPending,
                "applicationUrl" => $this->applicationUrl,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_job_application_reminder_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "job_title" => $this->jobTitle,
            "company_name" => $this->companyName,
            "days_pending" => $this->daysPending,
            "application_url" => $this->applicationUrl,
        ];
    }
}
