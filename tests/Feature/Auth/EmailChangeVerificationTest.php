<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\EmailVerificationTokenPurpose;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailChangeVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testValidTokenSwapsEmailAndClearsPendingEmail(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => "new@example.com",
        ]);
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => EmailVerificationTokenPurpose::EmailChange,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $response = $this->get("/email/change/confirm/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $user->refresh();
        $this->assertEquals("new@example.com", $user->email);
        $this->assertNull($user->pending_email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function testValidTokenIsInvalidatedSoItCannotBeReused(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => "new@example.com",
        ]);
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => EmailVerificationTokenPurpose::EmailChange,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $this->get("/email/change/confirm/{$user->id}/{$plainToken}");

        $this->assertDatabaseMissing("email_verification_tokens", [
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
        ]);
    }

    public function testExpiredTokenDoesNotChangeEmail(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => "new@example.com",
        ]);
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => EmailVerificationTokenPurpose::EmailChange,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->subSecond(),
        ]);

        $response = $this->get("/email/change/confirm/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $user->refresh();
        $this->assertEquals("old@example.com", $user->email);
        $this->assertEquals("new@example.com", $user->pending_email);
    }

    public function testInvalidTokenDoesNotChangeEmail(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => "new@example.com",
        ]);

        $response = $this->get("/email/change/confirm/{$user->id}/invalid-token");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $user->refresh();
        $this->assertEquals("old@example.com", $user->email);
        $this->assertEquals("new@example.com", $user->pending_email);
    }

    public function testTokenWithoutPendingEmailDoesNotChangeEmail(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => null,
        ]);
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => EmailVerificationTokenPurpose::EmailChange,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $response = $this->get("/email/change/confirm/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertEquals("old@example.com", $user->fresh()->email);
    }

    public function testRegistrationTokenCannotBeUsedToConfirmEmailChange(): void
    {
        $user = User::factory()->create([
            "email" => "old@example.com",
            "pending_email" => "new@example.com",
        ]);
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => EmailVerificationTokenPurpose::Registration,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $response = $this->get("/email/change/confirm/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $user->refresh();
        $this->assertEquals("old@example.com", $user->email);
        $this->assertEquals("new@example.com", $user->pending_email);
    }
}
