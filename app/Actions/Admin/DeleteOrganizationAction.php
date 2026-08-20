<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Company;
use App\Models\University;
use App\Models\User;

class DeleteOrganizationAction
{
    public function execute(Company|University $organization, User $admin): void
    {
        $organization->delete();

        activity()->causedBy($admin)
            ->performedOn($organization)
            ->withProperties(["name" => $organization->name])
            ->log($organization instanceof Company ? "company_deleted" : "university_deleted");
    }
}
