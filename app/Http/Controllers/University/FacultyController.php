<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\University\BuildFacultiesManagementData;
use App\Actions\University\CreateFaculty;
use App\Actions\University\DeleteFaculty;
use App\Actions\University\RenameFaculty;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteFacultyRequest;
use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class FacultyController extends Controller
{
    public function __construct(
        private readonly BuildFacultiesManagementData $buildFacultiesManagementData,
        private readonly CreateFaculty $createFaculty,
        private readonly RenameFaculty $renameFaculty,
        private readonly DeleteFaculty $deleteFaculty,
    ) {}

    public function index(): Response
    {
        Gate::authorize("viewAny", Faculty::class);

        return inertia("University/Faculties", [
            "faculties" => $this->buildFacultiesManagementData->execute(Auth::user()->universityOrganization),
        ]);
    }

    public function store(StoreFacultyRequest $request): RedirectResponse
    {
        Gate::authorize("create", Faculty::class);

        $this->createFaculty->execute(Auth::user()->universityOrganization, $request->string("name")->toString());

        return back();
    }

    public function update(UpdateFacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        Gate::authorize("update", $faculty);

        $this->renameFaculty->execute($faculty, $request->string("name")->toString());

        return back();
    }

    public function destroy(DeleteFacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        Gate::authorize("delete", $faculty);

        $reassignTo = $request->filled("reassign_to")
            ? Faculty::findOrFail($request->string("reassign_to")->toString())
            : null;

        $this->deleteFaculty->execute($faculty, $reassignTo);

        return back();
    }
}
