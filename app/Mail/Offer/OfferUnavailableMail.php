<?php

declare(strict_types=1);

namespace App\Mail\Offer;

use App\Mail\QueueableMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OfferUnavailableMail extends QueueableMailable
{
    public function __construct(
        public readonly string $jobTitle,
        public readonly string $companyName,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.offer.unavailable_subject", ["job_title" => $this->jobTitle]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.offer.unavailable",
            with: [
                "jobTitle" => $this->jobTitle,
                "companyName" => $this->companyName,
                "reasonText" => __("emails.offer.reason_" . $this->reason),
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_offer_unavailable_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "job_title" => $this->jobTitle,
            "reason" => $this->reason,
        ];
    }
}
