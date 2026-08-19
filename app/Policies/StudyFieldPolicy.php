<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\User;

class StudyFieldPolicy
{
    public function create(User $user, Faculty $faculty): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $faculty->university_id;
    }

    public function update(User $user, StudyField $studyField): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $studyField->faculty->university_id;
    }

    public function delete(User $user, StudyField $studyField): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $studyField->faculty->university_id;
    }
}
