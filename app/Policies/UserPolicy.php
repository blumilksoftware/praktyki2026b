<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class UserPolicy
{
    public function viewTeam(User $user): bool
    {
        $context = $this->teamContext($user);

        return $user->status === UserStatus::Active
            && $context["organization"] !== null
            && $context["organization"]->verification_status === VerificationStatus::Verified;
    }

    public function teamContext(User $user): array
    {
        return match ($user->role) {
            UserRole::CompanyAdmin, UserRole::CompanyMember => [
                "organization" => $user->company,
                "typeLabel" => "company",
                "invitationType" => OrganizationType::Company,
                "staffRoles" => [UserRole::CompanyAdmin, UserRole::CompanyMember],
            ],
            UserRole::UniversityAdmin, UserRole::UniversityMember => [
                "organization" => $user->universityOrganization,
                "typeLabel" => "university",
                "invitationType" => OrganizationType::University,
                "staffRoles" => [UserRole::UniversityAdmin, UserRole::UniversityMember],
            ],
            default => throw new AuthorizationException("Forbidden"),
        };
    }

    public function removeFromTeam(User $user, User $member): bool
    {
        return $this->administers($user, $member) !== null;
    }

    public function viewProfile(User $user, User $student): bool
    {
        if ($user->role === UserRole::SuperAdmin) {
            return true;
        }

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

    public function updateRole(User $admin, User $target): bool
    {
        return $admin->id !== $target->id;
    }

    public function updateStatus(User $admin, User $target): bool
    {
        return $admin->id !== $target->id;
    }

    public function deleteUser(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return false;
        }

        if ($target->role === UserRole::SuperAdmin && $this->isLastOrganizationMember($target)) {
            return false;
        }

        return true;
    }

    public function isLastOrganizationMember(User $user): bool
    {
        $organizationType = $user->role->organizationType();

        if ($organizationType === null) {
            return false;
        }

        return !User::query()
            ->where("organization_id", $user->organization_id)
            ->whereIn("role", [
                $organizationType->adminRole(),
                $organizationType->memberRole(),
            ])
            ->where("status", "!=", UserStatus::Deleted)
            ->whereKeyNot($user->getKey())
            ->exists();
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
