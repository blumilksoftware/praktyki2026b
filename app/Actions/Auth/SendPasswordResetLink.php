<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Mail\OAuthPasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLink
{
    public function execute(string $email): void
    {
        $user = User::where("email", $email)->first();

        if ($user === null) {
            return;
        }

        if ($user->google_id !== null) {
            Mail::to($email)->queue(new OAuthPasswordResetMail($user));

            return;
        }

        Password::sendResetLink(["email" => $email]);
    }
}
