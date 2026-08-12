<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveTeamMember
{
    public function execute(User $member): void
    {
        if ($this->isLastAdmin($member)) {
            throw ValidationException::withMessages([
                "member" => __("validation.last_organization_admin"),
            ]);
        }

        $member->delete();
    }

    private function isLastAdmin(User $member): bool
    {
        $adminRole = $member->role->organizationType()?->adminRole();

        if ($adminRole === null || $member->role !== $adminRole) {
            return false;
        }

        return !User::query()
            ->where("organization_id", $member->organization_id)
            ->where("role", $adminRole)
            ->whereKeyNot($member->getKey())
            ->exists();
    }
}
