<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StudentRegistrationMail extends QueueableMailable
{
    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.registration.subject"),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: "emails.student_registration",
        );
    }

    protected function getLogAction(): string
    {
        return "send_student_registration_mail";
    }

    protected function getLogProperties(): array
    {
        return [
            "user_id" => $this->user->id,
        ];
    }
}
