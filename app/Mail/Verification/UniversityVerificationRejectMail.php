<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Mail\QueueableMailable;
use App\Models\University;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UniversityVerificationRejectMail extends QueueableMailable
{
    public function __construct(
        public readonly University $university,
        public readonly string $rejectionReason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.verification.reject_mail_subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.verification.university_verification_reject",
            with: [
                "university" => $this->university,
                "rejectionReason" => $this->rejectionReason,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_university_verification_reject_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "university_id" => $this->university->id,
        ];
    }
}
