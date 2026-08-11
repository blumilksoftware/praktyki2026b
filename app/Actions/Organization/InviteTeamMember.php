<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteTeamMember
{
    public function execute(Company|University $organization, User $inviter, string $email): OrganizationInvitation
    {
        $plainToken = Str::random(64);

        $invitation = OrganizationInvitation::updateOrCreate(
            [
                "organization_id" => $organization->id,
                "organization_type" => OrganizationType::forOrganization($organization),
                "email" => $email,
            ],
            [
                "invited_by" => $inviter->id,
                "token" => hash("sha256", $plainToken),
                "status" => InvitationStatus::Pending,
                "accepted_at" => null,
                "revoked_at" => null,
                "expires_at" => now()->addMinutes(config("auth.invitation.expire", 10080)),
            ],
        );

        Mail::to($email)->queue(new TeamInvitationMail($organization->name, $plainToken));

        return $invitation;
    }
}
