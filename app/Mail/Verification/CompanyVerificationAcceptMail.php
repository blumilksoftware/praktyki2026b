<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Mail\QueueableMailable;
use App\Models\Company;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CompanyVerificationAcceptMail extends QueueableMailable
{
    public function __construct(
        public readonly Company $company,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.verification.accept_mail_subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.verification.company_verification_accept",
            with: [
                "company" => $this->company,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_company_verification_accept_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "company_id" => $this->company->id,
        ];
    }
}
