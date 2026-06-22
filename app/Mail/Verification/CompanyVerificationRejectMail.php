<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyVerificationRejectMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Company $company,
        public readonly string $rejectionReason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Company Verification Reject Mail",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.verification.company_verification_reject",
            with: [
                "company" => $this->company,
                "rejectionReason" => $this->rejectionReason,
            ],
        );
    }
}
