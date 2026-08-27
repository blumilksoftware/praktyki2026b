<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\City;
use App\Models\User;

class CreateCityAction
{
    public function execute(User $admin, string $name): City
    {
        $city = City::query()->create(["name" => $name]);

        activity()
            ->causedBy($admin)
            ->performedOn($city)
            ->withProperties(["name" => $city->name])
            ->log("city_created");

        return $city;
    }
}
