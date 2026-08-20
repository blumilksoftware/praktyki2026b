<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChangeUserStatusAction
{
    public function execute(User $admin, User $target, UserStatus $newStatus): void
    {
        DB::transaction(function () use ($admin, $target, $newStatus): void {
            $oldStatus = $target->status;

            $target->forceFill(["status" => $newStatus])->save();

            if ($newStatus === UserStatus::Blocked) {
                $target->tokens()->delete();
            }

            activity()->causedBy($admin)
                ->performedOn($target)
                ->withProperties([
                    "old_status" => $oldStatus->value,
                    "new_status" => $newStatus->value,
                ])
                ->log("user_status_changed");
        });
    }
}
