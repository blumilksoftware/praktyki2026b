<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetMail extends QueueableMailable
{
    public function __construct(
        public readonly User $user,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.password_reset.subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.password_reset",
            with: [
                "user" => $this->user,
                "url" => route("password.reset", [
                    "token" => $this->token,
                    "email" => $this->user->email,
                ]),
                "expiresInMinutes" => config("auth.passwords.users.expire"),
            ],
        );
    }

    protected function getLogAction(): string
    {
        return "send_password_reset_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "user_id" => $this->user->id,
        ];
    }
}
