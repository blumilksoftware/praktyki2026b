<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function __construct(
        private readonly LogOutUser $logOutUser,
    ) {}

    public function execute(Request $request, string $email, string $password, bool $remember = false): ?User
    {
        if (!Auth::attempt(["email" => $email, "password" => $password], $remember)) {
            throw ValidationException::withMessages([
                "email" => __("auth.failed"),
            ]);
        }

        $user = Auth::user();

        if ($user === null) {
            throw ValidationException::withMessages([
                "email" => __("auth.failed"),
            ]);
        }

        if ($user->status === UserStatus::Blocked) {
            $this->logOutUser->execute($request);

            throw ValidationException::withMessages([
                "email" => __("auth.blocked"),
            ]);
        }

        if (!$user->hasVerifiedEmail() || $user->status === UserStatus::Pending) {
            session()->flash("requires_verification", true);
            session()->flash("email", $user->email);

            return $user;
        }

        $request->session()->regenerate();

        return $user;
    }
}
