<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteOrganizationAction
{
    public function execute(Company|University $organization, User $admin): void
    {
        DB::transaction(function () use ($organization, $admin): void {
            if ($organization instanceof Company) {
                $organization->offers()->delete();
            }

            $organization->users()->update(["status" => UserStatus::Deleted]);

            $organization->delete();

            activity()->causedBy($admin)
                ->performedOn($organization)
                ->withProperties(["name" => $organization->name])
                ->log($organization instanceof Company ? "company_deleted" : "university_deleted");
        });
    }
}
