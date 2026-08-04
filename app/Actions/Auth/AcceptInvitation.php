<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\CompanyInvitationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function execute(string $token, string $firstName, string $lastName, string $password): User
    {
        $invitation = CompanyInvitation::where("token", hash("sha256", $token))->first();

        if ($invitation === null || $invitation->status !== CompanyInvitationStatus::Pending || $invitation->isExpired()) {
            throw ValidationException::withMessages([
                "token" => [__("auth.invitation.invalid_link")],
            ]);
        }

        if (User::where("email", $invitation->email)->exists()) {
            throw ValidationException::withMessages([
                "email" => [__("validation.email_taken")],
            ]);
        }

        return DB::transaction(function () use ($invitation, $firstName, $lastName, $password): User {
            $user = User::create([
                "first_name" => $firstName,
                "last_name" => $lastName,
                "email" => $invitation->email,
                "password" => $password,
                "role" => UserRole::CompanyMember,
                "status" => UserStatus::Active,
                "organization_id" => $invitation->company_id,
                "terms_accepted_at" => now(),
            ]);
            $user->markEmailAsVerified();

            $invitation->forceFill([
                "status" => CompanyInvitationStatus::Accepted,
                "accepted_at" => now(),
            ])->save();

            return $user;
        });
    }
}
