<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\InvitationStatus;
use App\Enums\UserStatus;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function execute(string $token, string $firstName, string $lastName, string $password): User
    {
        $invitation = OrganizationInvitation::where("token", hash("sha256", $token))->first();

        if ($invitation === null || $invitation->status !== InvitationStatus::Pending || $invitation->isExpired()) {
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
                "role" => $invitation->organization_type->memberRole(),
                "status" => UserStatus::Active,
                "organization_id" => $invitation->organization_id,
                "terms_accepted_at" => now(),
            ]);
            $user->markEmailAsVerified();

            $invitation->forceFill([
                "status" => InvitationStatus::Accepted,
                "accepted_at" => now(),
            ])->save();

            OrganizationInvitation::where("email", $invitation->email)
                ->where("status", InvitationStatus::Pending)
                ->whereKeyNot($invitation->getKey())
                ->update([
                    "status" => InvitationStatus::Revoked,
                    "revoked_at" => now(),
                ]);

            return $user;
        });
    }
}
