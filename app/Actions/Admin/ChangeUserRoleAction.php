<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChangeUserRoleAction
{
    public function execute(User $admin, User $target, UserRole $newRole): void
    {
        DB::transaction(function () use ($admin, $target, $newRole): void {
            $oldRole = $target->role;

            $target->forceFill(["role" => $newRole])->save();

            activity()->causedBy($admin)
                ->performedOn($target)
                ->withProperties(["old_role" => $oldRole->value, "new_role" => $newRole->value])
                ->log("user_role_changed");
        });
    }
}
