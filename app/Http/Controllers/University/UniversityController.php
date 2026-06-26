<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\University\UpdateUniversityProfile;
use App\DTO\University\UpdateUniversityProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUniversityProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class UniversityController extends Controller
{
    public function __construct(
        private readonly UpdateUniversityProfile $updateUniversityProfile,
    ) {}

    public function index(): Response
    {
        return inertia("University/Dashboard");
    }

    public function profile(): Response
    {
        return inertia("University/Profile", [
            "user" => Auth::user(),
        ]);
    }

    public function update(UpdateUniversityProfileRequest $request): RedirectResponse
    {
        $university = Auth::user()->universityOrganization;
        $data = UpdateUniversityProfileData::fromArray($request->getData());

        $this->updateUniversityProfile->execute($university, $data);

        return redirect()->route("university.profile");
    }
}
