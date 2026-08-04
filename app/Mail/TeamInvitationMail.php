<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TeamInvitationMail extends QueueableMailable
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.team_invitation.subject", ["company" => $this->companyName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.team_invitation",
            with: [
                "companyName" => $this->companyName,
                "token" => $this->token,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_team_invitation_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "company_name" => $this->companyName,
        ];
    }
}
