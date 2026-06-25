<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPassword
{
    public function execute(string $email, string $password, string $token): void
    {
        $status = Password::reset(
            ["email" => $email, "password" => $password, "password_confirmation" => $password, "token" => $token],
            function (User $user, string $password): void {
                $user->forceFill(["password" => Hash::make($password)])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                "email" => [__($status)],
            ]);
        }
    }
}
