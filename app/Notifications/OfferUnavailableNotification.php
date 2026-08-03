<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Notifications\Notification;

class OfferUnavailableNotification extends Notification
{
    public function __construct(
        private readonly Offer $offer,
        private readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "offer_unavailable",
            "offer_id" => $this->offer->id,
            "offer_title" => $this->offer->title,
            "reason" => $this->reason,
            "url" => route("student.applications"),
        ];
    }
}
