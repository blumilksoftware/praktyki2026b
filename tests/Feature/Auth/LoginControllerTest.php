<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function testPendingUserIsRedirectedToVerificationWaitingPage(): void
    {
        $user = User::factory()->create([
            "email" => "pending@example.com",
            "password" => Hash::make("password"),
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
            "email_verified_at" => null,
        ]);

        $response = $this->post(route("login.store"), [
            "email" => $user->email,
            "password" => "password",
        ]);

        $response->assertRedirect("/")
            ->assertSessionHasErrors("email");
        $this->assertGuest();
    }
}
