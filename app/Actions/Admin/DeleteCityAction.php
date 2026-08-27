<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\City;
use App\Models\User;

class DeleteCityAction
{
    public function execute(User $admin, City $city): void
    {
        $name = $city->name;

        $city->delete();

        activity()
            ->causedBy($admin)
            ->performedOn($city)
            ->withProperties(["name" => $name])
            ->log("city_deleted");
    }
}
