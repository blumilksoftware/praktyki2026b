<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\OrganizationInvitation;
use App\Models\User;

class OrganizationInvitationPolicy
{
    public function delete(User $user, OrganizationInvitation $invitation): bool
    {
        $organizationType = $user->role->organizationType();

        return $user->status === UserStatus::Active
            && $organizationType !== null
            && $user->role === $organizationType->adminRole()
            && $user->organization_id === $invitation->organization_id
            && $organizationType === $invitation->organization_type;
    }
}
