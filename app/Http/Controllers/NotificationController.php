<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\MarkAllNotificationsAsReadAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly MarkAllNotificationsAsReadAction $markAllNotificationsAsReadAction,
    ) {}

    public function markAllAsRead(): RedirectResponse
    {
        $this->markAllNotificationsAsReadAction->execute(Auth::user());

        return back();
    }
}
