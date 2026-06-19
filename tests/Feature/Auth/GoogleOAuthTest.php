<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function testNewUserCanRegisterViaGoogle(): void
    {
        $this->mockSocialiteCallback();

        $response = $this->get("/auth/google/callback");

        $response->assertRedirect("/home");
        $this->assertAuthenticated();
        $this->assertDatabaseHas("users", [
            "email" => "google@example.com",
            "google_id" => "google-123",
            "role" => UserRole::Student->value,
        ]);
    }

    public function testNewGoogleUserEmailIsVerified(): void
    {
        $this->mockSocialiteCallback();

        $this->get("/auth/google/callback");

        $user = User::where("email", "google@example.com")->first();
        $this->assertNotNull($user->email_verified_at);
    }

    public function testExistingUserWithGoogleIdCanLogin(): void
    {
        $user = User::factory()->create([
            "email" => "google@example.com",
            "google_id" => "google-123",
        ]);

        $this->mockSocialiteCallback();

        $response = $this->get("/auth/google/callback");

        $response->assertRedirect("/home");
        $this->assertAuthenticatedAs($user);
    }

    public function testExistingEmailAccountGetsLinkedOnGoogleLogin(): void
    {
        $user = User::factory()->unverified()->create([
            "email" => "google@example.com",
            "google_id" => null,
        ]);

        $this->mockSocialiteCallback();

        $response = $this->get("/auth/google/callback");

        $response->assertRedirect("/home");
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertNotNull($user->google_id);
        $this->assertNotNull($user->email_verified_at);
    }

    private function mockSocialiteCallback(
        string $googleId = "google-123",
        string $email = "google@example.com",
        ?string $firstName = "John",
        ?string $lastName = "Doe",
    ): void {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive("getId")->andReturn($googleId);
        $socialiteUser->shouldReceive("getEmail")->andReturn($email);
        $socialiteUser->shouldReceive("offsetGet")->with("given_name")->andReturn($firstName);
        $socialiteUser->shouldReceive("offsetGet")->with("family_name")->andReturn($lastName);

        $provider = Mockery::mock(AbstractProvider::class);
        $provider->shouldReceive("user")->andReturn($socialiteUser);

        Socialite::shouldReceive("driver")->with("google")->andReturn($provider);
    }
}
