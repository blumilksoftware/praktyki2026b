<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginIsBlockedAfterFiveFailedAttempts(): void
    {
        User::factory()->create([
            "email" => "user@example.com",
            "password" => "Password123!",
            "email_verified_at" => now(),
        ]);

        $this->attemptLogin(5);
        $response = $this->attemptLogin();

        $this->assertThrottled($response);
        $this->assertGuest();
    }

    public function testThrottledResponseIncludesRetryAfterHeader(): void
    {
        $this->attemptLogin(5);
        $response = $this->attemptLogin();

        $response->assertHeader("Retry-After");
        $this->assertGreaterThan(0, (int)$response->headers->get("Retry-After"));
    }

    public function testThrottledLoginKeepsUserOnFormWithError(): void
    {
        $this->attemptLogin(5);
        $response = $this->attemptLogin();

        $seconds = $response->headers->get("Retry-After");
        $response->assertSessionHasErrors(["email" => __("auth.throttle", ["seconds" => $seconds])]);
    }

    public function testThrottledJsonRequestKeepsStatusCode(): void
    {
        $this->attemptLogin(5);

        $this->postJson("/login", [
            "email" => "user@example.com",
            "password" => "wrong-password",
        ])->assertStatus(429);
    }

    public function testLoginLimitIsNotSharedBetweenAccounts(): void
    {
        $this->attemptLogin(5);

        $this->attemptLogin(email: "another@example.com")->assertSessionHasErrors("email");
        $this->assertSessionDoesNotHaveThrottleError();
    }

    public function testLoginLimitCountsAttemptsRegardlessOfEmailCasing(): void
    {
        $this->attemptLogin(5, email: "user@example.com");
        $response = $this->attemptLogin(email: "USER@Example.com");

        $this->assertThrottled($response);
    }

    public function testAdminLoginIsThrottled(): void
    {
        $this->attemptLogin(5, uri: "/admin/login");
        $response = $this->attemptLogin(uri: "/admin/login");

        $this->assertThrottled($response);
    }

    public function testForgotPasswordIsThrottled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post("/forgot-password", ["email" => "user@example.com"]);
        }

        $response = $this->post("/forgot-password", ["email" => "user@example.com"]);

        $this->assertThrottled($response);
    }

    public function testResetPasswordIsThrottled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post("/reset-password", ["email" => "user@example.com"]);
        }

        $response = $this->post("/reset-password", ["email" => "user@example.com"]);

        $this->assertThrottled($response);
    }

    public function testEmailVerificationResendIsThrottled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post("/email/resend", ["email" => "user@example.com"]);
        }

        $response = $this->post("/email/resend", ["email" => "user@example.com"]);

        $this->assertThrottled($response);
    }

    public function testStudentRegistrationIsThrottled(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post("/register/student", ["email" => "user@example.com"]);
        }

        $response = $this->post("/register/student", ["email" => "user@example.com"]);

        $this->assertThrottled($response);
    }

    private function attemptLogin(int $times = 1, string $email = "user@example.com", string $uri = "/login"): TestResponse
    {
        $response = null;

        for ($attempt = 0; $attempt < $times; $attempt++) {
            $response = $this->post($uri, [
                "email" => $email,
                "password" => "wrong-password",
            ]);
        }

        return $response;
    }

    private function assertThrottled(TestResponse $response): void
    {
        $response->assertRedirect();
        $response->assertHeader("Retry-After");
        $response->assertSessionHasErrors([
            "email" => __("auth.throttle", ["seconds" => $response->headers->get("Retry-After")]),
        ]);
    }

    private function assertSessionDoesNotHaveThrottleError(): void
    {
        $errors = session("errors")->getBag("default")->get("email");

        $this->assertNotContains(__("auth.throttle", ["seconds" => 60]), $errors);
    }
}
