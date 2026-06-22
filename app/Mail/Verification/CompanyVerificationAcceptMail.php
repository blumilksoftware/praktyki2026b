<?php

declare(strict_types=1);

namespace App\Mail\Verification;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyVerificationAcceptMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Company $company,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Company Verification Accept Mail",
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
}
