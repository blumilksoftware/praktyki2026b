<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Notification;

use App\Actions\Notification\MarkNotificationAsReadAction;
use App\Models\Company;
use App\Models\User;
use App\Notifications\NewVerificationRequestNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkNotificationAsReadActionTest extends TestCase
{
    use RefreshDatabase;

    public function testItMarksOwnNotificationAsRead(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $notification = $user->notifications()->first();

        (new MarkNotificationAsReadAction())->execute($user, $notification);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function testItRejectsMarkingAnotherUsersNotificationAsRead(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $notification = $owner->notifications()->first();

        $this->expectException(AuthorizationException::class);

        (new MarkNotificationAsReadAction())->execute($otherUser, $notification);
    }
}
