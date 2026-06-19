<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\EmailVerificationToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTokenTest extends TestCase
{
    use RefreshDatabase;

    public function testIsExpiredReturnsTrueWhenExpired(): void
    {
        $token = new EmailVerificationToken([
            "expires_at" => now()->subSecond(),
        ]);

        $this->assertTrue($token->isExpired());
    }

    public function testIsExpiredReturnsFalseWhenNotExpired(): void
    {
        $token = new EmailVerificationToken([
            "expires_at" => now()->addHour(),
        ]);

        $this->assertFalse($token->isExpired());
    }
}
