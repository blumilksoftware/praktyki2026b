<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLoginWithValidCredentials(): void
    {
        $user = User::factory()->create([
            "email" => "user@example.com",
            "password" => "Password123!",
            "email_verified_at" => now(),
        ]);

        $response = $this->post("/login", [
            "email" => "user@example.com",
            "password" => "Password123!",
        ]);

        $response->assertRedirect("/home");
        $this->assertAuthenticatedAs($user);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        User::factory()->create([
            "email" => "user@example.com",
            "password" => "Password123!",
            "email_verified_at" => now(),
        ]);

        $response = $this->post("/login", [
            "email" => "user@example.com",
            "password" => "wrong-password",
        ]);

        $response->assertSessionHasErrors(["email"]);
        $this->assertGuest();
    }

    public function testLoginFailsWithNonExistentEmail(): void
    {
        $response = $this->post("/login", [
            "email" => "nobody@example.com",
            "password" => "Password123!",
        ]);

        $response->assertSessionHasErrors(["email"]);
        $this->assertGuest();
    }

    public function testUnverifiedUserCannotLogin(): void
    {
        User::factory()->create([
            "email" => "user@example.com",
            "password" => "Password123!",
            "email_verified_at" => null,
        ]);

        $response = $this->post("/login", [
            "email" => "user@example.com",
            "password" => "Password123!",
        ]);

        $response->assertSessionHasErrors(["email"]);
        $this->assertGuest();
    }
}
