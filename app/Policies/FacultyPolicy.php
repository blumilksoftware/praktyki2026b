<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\Faculty;
use App\Models\User;

class FacultyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === UserStatus::Active
            && $user->universityOrganization !== null;
    }

    public function create(User $user): bool
    {
        return $user->status === UserStatus::Active
            && $user->universityOrganization !== null;
    }

    public function update(User $user, Faculty $faculty): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $faculty->university_id;
    }

    public function delete(User $user, Faculty $faculty): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $faculty->university_id;
    }
}
