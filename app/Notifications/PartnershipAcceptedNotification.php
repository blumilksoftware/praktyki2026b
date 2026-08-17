<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnershipAcceptedNotification extends Notification
{
    public function __construct(
        private readonly string $acceptorName,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "partnership_accepted",
            "acceptor_name" => $this->acceptorName,
            "url" => $this->url,
        ];
    }
}
