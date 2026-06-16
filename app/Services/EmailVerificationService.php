<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function sendVerificationEmail(User $user): void
    {
        $token = $this->createToken($user);
        Mail::to($user->email)->send(new EmailVerificationMail($user, $token));
    }

    public function verify(User $user, string $token): bool
    {
        $record = $user->verificationTokens()
            ->where("token", hash("sha256", $token))
            ->first();

        if ($record === null || $record->isExpired()) {
            return false;
        }

        $record->delete();
        $user->markEmailAsVerified();

        return true;
    }

    private function createToken(User $user): string
    {
        $user->verificationTokens()->delete();

        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addMinutes(config("auth.verification.expire", 1440)),
        ]);

        return $plainToken;
    }
}
