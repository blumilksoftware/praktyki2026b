<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\City;
use App\Models\User;

class UpdateCityAction
{
    public function execute(User $admin, City $city, string $name): void
    {
        $oldName = $city->name;

        $city->update(["name" => $name]);

        activity()
            ->causedBy($admin)
            ->performedOn($city)
            ->withProperties(["old_name" => $oldName, "new_name" => $city->name])
            ->log("city_updated");
    }
}
