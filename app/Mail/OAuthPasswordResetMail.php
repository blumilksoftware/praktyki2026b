<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OAuthPasswordResetMail extends QueueableMailable
{
    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.password_reset.oauth_subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.oauth_password_reset",
            with: [
                "user" => $this->user,
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_oauth_password_reset_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "user_id" => $this->user->id,
        ];
    }
}
