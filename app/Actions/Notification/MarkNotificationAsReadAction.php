<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;

class MarkNotificationAsReadAction
{
    public function execute(User $user, DatabaseNotification $notification): void
    {
        Gate::forUser($user)->authorize("markAsRead", $notification);

        $notification->markAsRead();
    }
}
