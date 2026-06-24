<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Mail\QueueableMailable;
use App\Models\Company;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CompanyVerificationRejectMail extends QueueableMailable
{
    public function __construct(
        public readonly Company $company,
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
            markdown: "emails.verification.company_verification_reject",
            with: [
                "company" => $this->company,
                "rejectionReason" => $this->rejectionReason,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_company_verification_reject_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "company_id" => $this->company->id,
        ];
    }
}
