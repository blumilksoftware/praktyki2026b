<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\MarkAllNotificationsAsReadAction;
use App\Actions\Notification\MarkNotificationAsReadAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly MarkAllNotificationsAsReadAction $markAllNotificationsAsReadAction,
        private readonly MarkNotificationAsReadAction $markNotificationAsReadAction,
    ) {}

    public function markAllAsRead(): RedirectResponse
    {
        $this->markAllNotificationsAsReadAction->execute(Auth::user());

        return back();
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        $this->markNotificationAsReadAction->execute(Auth::user(), $notification);

        return redirect($notification->data["url"] ?? "/");
    }
}
