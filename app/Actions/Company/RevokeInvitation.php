<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanyInvitationStatus;
use App\Models\CompanyInvitation;
use Illuminate\Validation\ValidationException;

class RevokeInvitation
{
    public function execute(CompanyInvitation $invitation): void
    {
        if ($invitation->status !== CompanyInvitationStatus::Pending) {
            throw ValidationException::withMessages([
                "status" => __("validation.invitation_already_processed"),
            ]);
        }

        $invitation->forceFill([
            "status" => CompanyInvitationStatus::Revoked,
            "revoked_at" => now(),
        ])->save();
    }
}
