<?php

declare(strict_types=1);

namespace App\Mail\JobApplication;

use App\Mail\QueueableMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewJobApplicationMail extends QueueableMailable
{
    public function __construct(
        public readonly string $studentName,
        public readonly string $jobTitle,
        public readonly string $applicationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.job_application.new_subject", ["job_title" => $this->jobTitle]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.job_application.new_application",
            with: [
                "studentName" => $this->studentName,
                "jobTitle" => $this->jobTitle,
                "applicationUrl" => $this->applicationUrl,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_new_job_application_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "student_name" => $this->studentName,
            "job_title" => $this->jobTitle,
            "application_url" => $this->applicationUrl,
        ];
    }
}
