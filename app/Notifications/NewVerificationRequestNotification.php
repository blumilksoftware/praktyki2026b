<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Company;
use App\Models\University;
use Illuminate\Notifications\Notification;

class NewVerificationRequestNotification extends Notification
{
    public function __construct(
        private readonly Company|University $entity,
    ) {}

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "type" => "verification_request",
            "entity_type" => $this->entity instanceof Company ? "company" : "university",
            "entity_id" => $this->entity->id,
            "entity_name" => $this->entity->name,
        ];
    }
}
