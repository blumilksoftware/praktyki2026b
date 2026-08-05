<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanyInvitationStatus;
use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteTeamMember
{
    public function execute(Company $company, User $inviter, string $email): CompanyInvitation
    {
        $plainToken = Str::random(64);

        $invitation = CompanyInvitation::updateOrCreate(
            [
                "company_id" => $company->id,
                "email" => $email,
            ],
            [
                "invited_by" => $inviter->id,
                "token" => hash("sha256", $plainToken),
                "status" => CompanyInvitationStatus::Pending,
                "accepted_at" => null,
                "revoked_at" => null,
                "expires_at" => now()->addMinutes(config("auth.invitation.expire", 10080)),
            ],
        );

        Mail::to($email)->queue(new TeamInvitationMail($company->name, $plainToken));

        return $invitation;
    }
}
