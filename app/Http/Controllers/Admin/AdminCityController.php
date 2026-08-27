<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateCityAction;
use App\Actions\Admin\DeleteCityAction;
use App\Actions\Admin\UpdateCityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class AdminCityController extends Controller
{
    public function __construct(
        private readonly CreateCityAction $createCityAction,
        private readonly UpdateCityAction $updateCityAction,
        private readonly DeleteCityAction $deleteCityAction,
    ) {}

    public function index(): Response
    {
        return inertia("Admin/Cities", [
            "cities" => City::query()->orderBy("name")->get(["id", "name"]),
        ]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $this->createCityAction->execute(Auth::user(), $request->string("name")->toString());

        return back();
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $this->updateCityAction->execute(Auth::user(), $city, $request->string("name")->toString());

        return back();
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->deleteCityAction->execute(Auth::user(), $city);

        return back();
    }
}
