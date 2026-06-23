<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifyWithValidTokenActivatesAccountAndRedirectsToLogin(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing("email_verification_tokens", ["user_id" => $user->id]);
    }

    public function testVerifyWithValidTokenInvalidatesItSoItCannotBeReused(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $this->get("/email/verify/{$user->id}/{$plainToken}");

        $this->assertDatabaseMissing("email_verification_tokens", [
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
        ]);
    }

    public function testVerifyWithExpiredTokenDoesNotActivateAccount(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->subSecond(),
        ]);

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function testVerifyWithInvalidTokenDoesNotActivateAccount(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->get("/email/verify/{$user->id}/invalid-token");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function testVerifyAlreadyConfirmedAccountRedirectsToLoginWithoutError(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/email/verify/{$user->id}/any-token");

        $response->assertRedirect("/login");
        $response->assertSessionMissing("errors");
    }

    public function testResendSendsNewVerificationEmail(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->post("/email/resend", ["email" => $user->email]);

        $response->assertRedirect();
        Mail::assertQueued(EmailVerificationMail::class, fn($mail): bool => $mail->hasTo($user->email));
    }

    public function testResendInvalidatesPreviousToken(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $oldToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $oldToken),
            "expires_at" => now()->addHours(24),
        ]);

        $this->post("/email/resend", ["email" => $user->email]);

        $this->assertDatabaseMissing("email_verification_tokens", [
            "user_id" => $user->id,
            "token" => hash("sha256", $oldToken),
        ]);

        $this->assertDatabaseCount("email_verification_tokens", 1);
    }

    public function testResendForAlreadyVerifiedUserDoesNotSendEmail(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post("/email/resend", ["email" => $user->email]);

        $response->assertRedirect();
        Mail::assertNotSent(EmailVerificationMail::class);
    }

    public function testResendForNonExistentEmailDoesNotSendEmail(): void
    {
        Mail::fake();

        $response = $this->post("/email/resend", ["email" => "nonexistent@example.com"]);

        $response->assertRedirect();
        Mail::assertNotSent(EmailVerificationMail::class);
    }

    public function testResendRequiresEmailField(): void
    {
        $response = $this->post("/email/resend", []);

        $response->assertRedirect();
        $response->assertSessionHasErrors("email");
    }

    public function testTokenExpiresAfter24Hours(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24)->subSecond(),
        ]);

        $this->travel(24)->hours();

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
