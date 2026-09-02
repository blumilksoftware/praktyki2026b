<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateIndustryTagAction;
use App\Actions\Admin\DeleteIndustryTagAction;
use App\Actions\Admin\UpdateIndustryTagAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIndustryTagRequest;
use App\Http\Requests\UpdateIndustryTagRequest;
use App\Models\IndustryTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class AdminIndustryTagController extends Controller
{
    public function __construct(
        private readonly CreateIndustryTagAction $createIndustryTagAction,
        private readonly UpdateIndustryTagAction $updateIndustryTagAction,
        private readonly DeleteIndustryTagAction $deleteIndustryTagAction,
    ) {}

    public function index(): Response
    {
        return inertia("Admin/IndustryTags", [
            "industryTags" => IndustryTag::query()->orderBy("name")->get(["id", "name"]),
        ]);
    }

    public function store(StoreIndustryTagRequest $request): RedirectResponse
    {
        $this->createIndustryTagAction->execute(Auth::user(), $request->string("name")->toString());

        return back();
    }

    public function update(UpdateIndustryTagRequest $request, IndustryTag $industryTag): RedirectResponse
    {
        $this->updateIndustryTagAction->execute(Auth::user(), $industryTag, $request->string("name")->toString());

        return back();
    }

    public function destroy(IndustryTag $industryTag): RedirectResponse
    {
        $this->deleteIndustryTagAction->execute(Auth::user(), $industryTag);

        return back();
    }
}
