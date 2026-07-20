<?php

declare(strict_types=1);

namespace App\Mail\JobApplication;

use App\Mail\QueueableMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class JobApplicationStatusChangedMail extends QueueableMailable
{
    public function __construct(
        public readonly string $jobTitle,
        public readonly string $companyName,
        public readonly string $status,
        public readonly string $dashboardUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.job_application.status_changed_subject", ["job_title" => $this->jobTitle]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.job_application.status_changed",
            with: [
                "jobTitle" => $this->jobTitle,
                "companyName" => $this->companyName,
                "status" => $this->status,
                "dashboardUrl" => $this->dashboardUrl,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_job_application_status_changed_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "job_title" => $this->jobTitle,
            "company_name" => $this->companyName,
            "status" => $this->status,
        ];
    }
}
