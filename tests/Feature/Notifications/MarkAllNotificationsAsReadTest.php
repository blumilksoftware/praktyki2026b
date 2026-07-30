<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Company;
use App\Models\University;
use App\Models\User;
use App\Notifications\NewVerificationRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAllNotificationsAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function testItMarksAllUnreadNotificationsAsReadInOneRequest(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $user->notify(new NewVerificationRequestNotification(University::factory()->create()));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->patch("/notifications/read-all")
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(2, $user->notifications()->whereNotNull("read_at")->count());
    }

    public function testUnauthenticatedUserCannotMarkNotificationsAsRead(): void
    {
        $this->patch("/notifications/read-all")
            ->assertRedirect("/login");
    }

    public function testItDoesNotMarkAnotherUsersNotificationsAsRead(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->notify(new NewVerificationRequestNotification(Company::factory()->create()));

        $this->actingAs($user)
            ->patch("/notifications/read-all")
            ->assertRedirect();

        $this->assertSame(1, $otherUser->unreadNotifications()->count());
    }

    public function testItDoesNothingWhenUserHasNoNotifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch("/notifications/read-all")
            ->assertRedirect();

        $this->assertSame(0, $user->notifications()->count());
    }
}
