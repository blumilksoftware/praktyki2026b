<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function execute(Request $request, string $email, string $password): void
    {
        if (!Auth::attempt(["email" => $email, "password" => $password])) {
            throw ValidationException::withMessages([
                "email" => __("auth.failed"),
            ]);
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            Auth::logout();

            throw ValidationException::withMessages([
                "email" => __("auth.verification.not_verified"),
            ]);
        }

        $request->session()->regenerate();
    }
}
