<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class ApplicationStatusChangedNotification extends Notification
{
    public function __construct(
        private readonly Application $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "application_status_changed",
            "application_id" => $this->application->id,
            "offer_id" => $this->application->offer_id,
            "offer_title" => $this->application->offer->title,
            "status" => $this->application->status->value,
        ];
    }
}
