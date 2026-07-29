<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class NewApplicationNotification extends Notification
{
    public function __construct(
        private readonly Application $application,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["database"];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "type" => "new_application",
            "application_id" => $this->application->id,
            "offer_id" => $this->application->offer_id,
            "offer_title" => $this->application->offer->title,
            "student_name" => $this->application->student->fullName(),
        ];
    }
}