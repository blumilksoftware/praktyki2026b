<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CompanyInvitation;
use App\Models\User;

class CompanyInvitationPolicy
{
    public function delete(User $user, CompanyInvitation $invitation): bool
    {
        return $user->status === UserStatus::Active
            && $user->role === UserRole::CompanyAdmin
            && $user->organization_id === $invitation->company_id;
    }
}
