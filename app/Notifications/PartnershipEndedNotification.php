<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnershipEndedNotification extends Notification
{
    public function __construct(
        private readonly string $enderName,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "partnership_ended",
            "ender_name" => $this->enderName,
            "url" => $this->url,
        ];
    }
}
