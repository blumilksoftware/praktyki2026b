<?php

declare(strict_types=1);

namespace App\Mail\Partnership;

use App\Mail\QueueableMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PartnershipRequestedMail extends QueueableMailable
{
    public function __construct(
        public readonly string $proposerName,
        public readonly string $dashboardUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.partnership.requested_subject", ["proposer_name" => $this->proposerName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.partnership.requested",
            with: [
                "proposerName" => $this->proposerName,
                "dashboardUrl" => $this->dashboardUrl,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_partnership_requested_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "proposer_name" => $this->proposerName,
        ];
    }
}
