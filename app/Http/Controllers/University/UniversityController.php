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

    public function verificationPending(): Response
    {
        return inertia("Auth/VerificationPending", [
            "user" => Auth::user(),
        ]);
    }

    public function profile(): Response
    {
        return inertia("University/Profile/Show", [
            "university" => $this->getUniversityProfileData(),
            "canEdit" => true,
        ]);
    }

    public function edit(): Response
    {
        return inertia("University/Profile/Edit", [
            "university" => $this->getUniversityProfileData(),
        ]);
    }

    public function update(UpdateUniversityProfileRequest $request): RedirectResponse
    {
        $university = Auth::user()->universityOrganization;
        $data = UpdateUniversityProfileData::fromArray($request->getData());

        $this->updateUniversityProfile->execute($university, $data);

        return redirect()->route("university.profile");
    }

    private function getUniversityProfileData(): array
    {
        $user = Auth::user();
        $university = $user->universityOrganization;

        return [
            "id" => $university?->id ?? $user->id,
            "name" => $university?->name ?? ($user->first_name . " " . $user->last_name),
            "logoUrl" => $university?->logo_path ?? null,
            "email" => $university?->email ?? null,
            "domain" => $university?->domain ?? null,            
            "street" => $university?->street ?? null,
            "postalCode" => $university?->postal_code ?? null,
            "city" => $university?->city ?? null,          
            "phone" => $university?->phone ?? null,
            "domain" => $university?->domain ?? null,
            "website" => $university?->website ?? null,
            "externalFormUrl" => $university?->external_form_url ?? null,
            "faculties" => $university ? $university->faculties()->with("studyFields")->get() : [],
        ];
    }
}
