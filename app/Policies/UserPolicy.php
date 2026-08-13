<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class UserPolicy
{
    public function removeFromTeam(User $user, User $member): bool
    {
        return $this->administers($user, $member) !== null;
    }

    public function viewProfile(User $user, User $student): bool
    {
        $company = $user->company;

        if ($company === null || $student->role !== UserRole::Student) {
            return false;
        }

        return $company->applications()
            ->where("applications.student_id", $student->id)
            ->exists();
    }

    public function transferOwnershipTo(User $user, User $member): bool
    {
        $organizationType = $this->administers($user, $member);

        return $organizationType !== null
            && $member->status === UserStatus::Active
            && $member->role === $organizationType->memberRole();
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
