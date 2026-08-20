<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChangeUserRoleAction
{
    public function execute(User $admin, User $target, UserRole $newRole, ?string $organizationId = null): void
    {
        DB::transaction(function () use ($admin, $target, $newRole, $organizationId): void {
            $oldRole = $target->role;
            $oldOrganizationId = $target->organization_id;
            $newOrganizationId = $newRole->organizationType() === null ? null : $organizationId;

            $target->forceFill([
                "role" => $newRole,
                "organization_id" => $newOrganizationId,
            ])->save();

            activity()->causedBy($admin)
                ->performedOn($target)
                ->withProperties([
                    "old_role" => $oldRole->value,
                    "new_role" => $newRole->value,
                    "old_organization_id" => $oldOrganizationId,
                    "new_organization_id" => $newOrganizationId,
                ])
                ->log("user_role_changed");
        });
    }
}
