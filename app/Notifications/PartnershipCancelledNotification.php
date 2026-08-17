<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnershipCancelledNotification extends Notification
{
    public function __construct(
        private readonly string $cancellerName,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "partnership_cancelled",
            "canceller_name" => $this->cancellerName,
            "url" => $this->url,
        ];
    }
}
