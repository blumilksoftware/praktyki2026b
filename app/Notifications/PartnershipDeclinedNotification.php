<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnershipDeclinedNotification extends Notification
{
    public function __construct(
        private readonly string $declinerName,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "partnership_declined",
            "decliner_name" => $this->declinerName,
            "url" => $this->url,
        ];
    }
}
