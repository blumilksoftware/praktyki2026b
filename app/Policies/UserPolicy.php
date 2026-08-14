<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationType;
use App\Enums\UserStatus;
use App\Models\User;

class UserPolicy
{
    public function removeFromTeam(User $user, User $member): bool
    {
        return $this->administers($user, $member) !== null;
    }

    public function transferOwnershipTo(User $user, User $member): bool
    {
        $organizationType = $this->administers($user, $member);

        return $organizationType !== null
            && $member->status === UserStatus::Active
            && $member->role === $organizationType->memberRole();
    }

    public function updateRole(User $admin, User $target): bool
    {
        return $admin->id !== $target->id;
    }

    private function administers(User $user, User $member): ?OrganizationType
    {
        $organizationType = $user->role->organizationType();

        $administers = $user->status === UserStatus::Active
            && $organizationType !== null
            && $user->role === $organizationType->adminRole()
            && $user->organization_id !== null
            && $user->organization_id === $member->organization_id
            && $organizationType === $member->role->organizationType()
            && $user->id !== $member->id;

        return $administers ? $organizationType : null;
    }
}
