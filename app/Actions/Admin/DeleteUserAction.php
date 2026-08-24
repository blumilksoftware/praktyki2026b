<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Actions\Organization\DeleteOrganizationUserAction;
use App\Actions\Student\DeleteStudentAccount;
use App\Enums\UserRole;
use App\Models\User;

class DeleteUserAction
{
    public function __construct(
        private readonly DeleteStudentAccount $deleteStudentAccount,
        private readonly DeleteOrganizationUserAction $deleteOrganizationUser,
    ) {}

    public function execute(User $user): void
    {
        match ($user->role) {
            UserRole::Student => $this->deleteStudentAccount->execute($user),
            UserRole::UniversityMember, UserRole::UniversityAdmin,
            UserRole::CompanyMember, UserRole::CompanyAdmin => $this->deleteOrganizationUser->execute($user),
        };
    }
}
