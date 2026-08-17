<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnershipRequestedNotification extends Notification
{
    public function __construct(
        private readonly string $proposerName,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "partnership_requested",
            "proposer_name" => $this->proposerName,
            "url" => $this->url,
        ];
    }
}
