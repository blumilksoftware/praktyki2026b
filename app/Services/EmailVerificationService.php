<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmailVerificationTokenPurpose;
use App\Enums\UserStatus;
use App\Mail\EmailChangeVerificationMail;
use App\Mail\EmailVerificationMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function sendVerificationEmail(User $user): void
    {
        $token = $this->createToken($user, EmailVerificationTokenPurpose::Registration);
        Mail::to($user->email)->send(new EmailVerificationMail($user, $token));
    }

    public function verify(User $user, string $token): bool
    {
        $record = $user->verificationTokens()
            ->where("purpose", EmailVerificationTokenPurpose::Registration)
            ->where("token", hash("sha256", $token))
            ->first();

        if ($record === null || $record->isExpired()) {
            return false;
        }

        $record->delete();
        $user->markEmailAsVerified();
        $user->status = UserStatus::Active;
        $user->save();

        return true;
    }

    public function sendEmailChangeVerification(User $user, string $newEmail): void
    {
        $token = $this->createToken($user, EmailVerificationTokenPurpose::EmailChange);
        Mail::to($newEmail)->send(new EmailChangeVerificationMail($user, $newEmail, $token));
    }

    public function confirmEmailChange(User $user, string $token): bool
    {
        $record = $user->verificationTokens()
            ->where("purpose", EmailVerificationTokenPurpose::EmailChange)
            ->where("token", hash("sha256", $token))
            ->first();

        if ($record === null || $record->isExpired() || $user->pending_email === null) {
            return false;
        }

        $record->delete();

        try {
            $user->forceFill([
                "email" => $user->pending_email,
                "pending_email" => null,
                "email_verified_at" => now(),
            ])->save();
        } catch (QueryException) {
            return false;
        }

        return true;
    }

    private function createToken(User $user, EmailVerificationTokenPurpose $purpose): string
    {
        $user->verificationTokens()->where("purpose", $purpose)->delete();

        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "purpose" => $purpose,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addMinutes(config("auth.verification.expire", 1440)),
        ]);

        return $plainToken;
    }
}
