<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Models\University;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UniversityVerificationRejectMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly University $university,
        public readonly string $rejectionReason,
    ) {}

    public function failed(Throwable $exception): void
    {
        activity()->causedByAnonymous()
            ->withProperties([
                "action" => "send_university_verification_reject_mail",
                "university_id" => $this->university->id,
                "exception_message" => $exception->getMessage(),
            ])
            ->log("failed_to_send_university_verification_reject_mail");
    }

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
