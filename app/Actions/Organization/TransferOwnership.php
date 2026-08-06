<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferOwnership
{
    public function execute(User $currentAdmin, User $newAdmin): void
    {
        DB::transaction(function () use ($currentAdmin, $newAdmin): void {
            $adminRole = $currentAdmin->role;
            $memberRole = $newAdmin->role;

            $currentAdmin->forceFill(["role" => $memberRole])->save();
            $newAdmin->forceFill(["role" => $adminRole])->save();
        });
    }
}
