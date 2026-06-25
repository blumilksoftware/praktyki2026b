<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\OAuthPasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Notification::fake();
    }

    public function testForgotPasswordReturnsSameResponseForAnyEmail(): void
    {
        User::factory()->create(["email" => "user@example.com"]);

        $this->post("/forgot-password", ["email" => "user@example.com"])
            ->assertSessionHas("status", __("passwords.sent"));

        $this->post("/forgot-password", ["email" => "nonexistent@example.com"])
            ->assertSessionHas("status", __("passwords.sent"));
    }

    public function testForgotPasswordForGoogleAccountReturnsSameResponse(): void
    {
        User::factory()->create([
            "email" => "user@example.com",
            "google_id" => "google-123",
        ]);

        $this->post("/forgot-password", ["email" => "user@example.com"])
            ->assertSessionHas("status", __("passwords.sent"));

        Mail::assertQueued(OAuthPasswordResetMail::class);
    }

    public function testExpiredTokenCannotBeUsed(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        DB::table("password_reset_tokens")->where("email", $user->email)->update([
            "created_at" => now()->subMinutes(61),
        ]);

        $this->post("/reset-password", [
            "token" => $token,
            "email" => $user->email,
            "password" => "NewPassword1!",
            "password_confirmation" => "NewPassword1!",
        ])->assertSessionHasErrors(["email"]);
    }

    public function testTokenCannotBeReused(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $data = [
            "token" => $token,
            "email" => $user->email,
            "password" => "NewPassword1!",
            "password_confirmation" => "NewPassword1!",
        ];

        $this->post("/reset-password", $data)->assertRedirect(route("login"));
        $this->post("/reset-password", $data)->assertSessionHasErrors(["email"]);
    }

    public function testUserCanLoginAfterPasswordReset(): void
    {
        $user = User::factory()->create([
            "email" => "user@example.com",
            "email_verified_at" => now(),
        ]);
        $token = Password::broker()->createToken($user);

        $this->post("/reset-password", [
            "token" => $token,
            "email" => $user->email,
            "password" => "NewPassword1!",
            "password_confirmation" => "NewPassword1!",
        ]);

        $this->post("/login", [
            "email" => $user->email,
            "password" => "NewPassword1!",
        ])->assertRedirect("/home");

        $this->assertAuthenticated();
    }
}
