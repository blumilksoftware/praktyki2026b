<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class StudentRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly User $user,
    ) {}

    public function failed(Throwable $exception): void
    {
        activity()->causedByAnonymous()
            ->withProperties([
                "action" => "send_student_registration_mail",
                "user_id" => $this->user->id,
                "exception_message" => $exception->getMessage(),
            ])
            ->log("failed_to_send_student_registration_mail");
    }

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
}
