<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Enums\InvitationStatus;
use App\Models\OrganizationInvitation;
use Illuminate\Validation\ValidationException;

class RevokeInvitation
{
    public function execute(OrganizationInvitation $invitation): void
    {
        if ($invitation->status !== InvitationStatus::Pending) {
            throw ValidationException::withMessages([
                "status" => __("validation.invitation_already_processed"),
            ]);
        }

        $invitation->forceFill([
            "status" => InvitationStatus::Revoked,
            "revoked_at" => now(),
        ])->save();
    }
}
