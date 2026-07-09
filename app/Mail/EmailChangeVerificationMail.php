<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmailChangeVerificationMail extends QueueableMailable
{
    public function __construct(
        public readonly User $user,
        public readonly string $newEmail,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.email_change.subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.email_change_verification",
            with: [
                "user" => $this->user,
                "newEmail" => $this->newEmail,
                "token" => $this->token,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_email_change_verification_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "user_id" => $this->user->id,
        ];
    }
}
