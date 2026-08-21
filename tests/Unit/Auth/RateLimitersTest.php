<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitersTest extends TestCase
{
    public function testLoginLimiterAllowsFiveAttemptsPerMinute(): void
    {
        $limit = $this->resolveLimit("login", "user@example.com");

        $this->assertSame(5, $limit->maxAttempts);
        $this->assertSame(60, $limit->decaySeconds);
    }

    public function testPasswordResetLimiterAllowsFiveAttemptsPerFifteenMinutes(): void
    {
        $limit = $this->resolveLimit("password-reset", "user@example.com");

        $this->assertSame(5, $limit->maxAttempts);
        $this->assertSame(900, $limit->decaySeconds);
    }

    public function testEmailVerificationLimiterAllowsFiveAttemptsPerFifteenMinutes(): void
    {
        $limit = $this->resolveLimit("email-verification", "user@example.com");

        $this->assertSame(5, $limit->maxAttempts);
        $this->assertSame(900, $limit->decaySeconds);
    }

    public function testLimiterKeyIgnoresEmailCasing(): void
    {
        $lowercase = $this->resolveLimit("login", "user@example.com");
        $uppercase = $this->resolveLimit("login", "USER@Example.com");

        $this->assertSame($lowercase->key, $uppercase->key);
    }

    public function testLimiterKeySeparatesDifferentEmails(): void
    {
        $first = $this->resolveLimit("login", "first@example.com");
        $second = $this->resolveLimit("login", "second@example.com");

        $this->assertNotSame($first->key, $second->key);
    }

    public function testLimiterKeySeparatesDifferentAddresses(): void
    {
        $first = $this->resolveLimit("login", "user@example.com", "10.0.0.1");
        $second = $this->resolveLimit("login", "user@example.com", "10.0.0.2");

        $this->assertNotSame($first->key, $second->key);
    }

    private function resolveLimit(string $name, string $email, string $ip = "127.0.0.1"): Limit
    {
        $limiter = RateLimiter::limiter($name);

        return $limiter(Request::create("/", "POST", ["email" => $email], server: ["REMOTE_ADDR" => $ip]));
    }
}
