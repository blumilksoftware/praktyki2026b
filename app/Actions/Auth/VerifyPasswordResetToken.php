<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class VerifyPasswordResetToken
{
    public function execute(string $email, string $token): bool
    {
        $user = User::where("email", $email)->first();

        return $user !== null && Password::broker()->tokenExists($user, $token);
    }
}
