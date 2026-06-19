<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthenticatedUserCanLogout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post("/logout");

        $response->assertRedirect("/");
        $this->assertGuest();
    }

    public function testLogoutSucceedsWhenAlreadyLoggedOut(): void
    {
        $response = $this->post("/logout");

        $response->assertRedirect();
    }
}
