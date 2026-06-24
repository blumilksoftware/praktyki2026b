<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Models\University;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UniversityVerificationRejectMail extends Mailable
{
    use Queueable;
    use SerializesModels;

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
}
