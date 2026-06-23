<?php

declare(strict_types=1);

namespace App\Mail\JobApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewJobApplicationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

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
}
