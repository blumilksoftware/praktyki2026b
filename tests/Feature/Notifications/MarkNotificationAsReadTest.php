<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Company;
use App\Models\User;
use App\Notifications\NewVerificationRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkNotificationAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function testItMarksNotificationAsReadAndRedirectsToItsUrl(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->patch("/notifications/{$notification->id}/read")
            ->assertRedirect(route("admin.applications"));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function testUserCannotMarkAnotherUsersNotificationAsRead(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $notification = $owner->notifications()->first();

        $this->actingAs($otherUser)
            ->patch("/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function testUnauthenticatedUserCannotMarkNotificationAsRead(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewVerificationRequestNotification(Company::factory()->create()));
        $notification = $user->notifications()->first();

        $this->patch("/notifications/{$notification->id}/read")
            ->assertRedirect("/login");
    }
}
