<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\GoogleUserData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandleGoogleCallback
{
    public function execute(GoogleUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::where("google_id", $data->googleId)->first()
                ?? User::where("email", $data->email)->first();

            if ($user === null) {
                $user = User::create([
                    "first_name" => $data->firstName,
                    "last_name" => $data->lastName,
                    "email" => $data->email,
                    "password" => Str::random(32),
                    "role" => UserRole::Student,
                    "google_id" => $data->googleId,
                    "terms_accepted_at" => now(),
                ]);

                $user->markEmailAsVerified();

                return $user;
            }

            if ($user->google_id === null) {
                $user->update(["google_id" => $data->googleId]);
                $user->markEmailAsVerified();
            }

            return $user;
        });
    }
}
