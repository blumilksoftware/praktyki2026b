<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly UpdateCompanyProfile $updateCompanyProfile,
    ) {}

    public function index(): Response
    {
        return inertia("Company/Dashboard");
    }

    public function verificationPending(): Response
    {
        return inertia("Auth/VerificationPending", [
            "user" => Auth::user(),
        ]);
    }

    public function profile(): Response
    {
        return inertia("Company/Profile/Show", [
            "company" => $this->getCompanyProfileData(),
            "canEdit" => true,
        ]);
    }

    public function edit(): Response
    {
        return inertia("Company/Profile/Edit", [
            "company" => $this->getCompanyProfileData(),
        ]);
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;
        $data = UpdateCompanyProfileData::fromArray($request->getData());

        $this->updateCompanyProfile->execute($company, $data);

        return redirect()->route("company.profile");
    }

    private function getCompanyProfileData(): array
    {
        $user = Auth::user();
        $company = $user->company;

        return [
            "id" => $company?->id ?? $user->id,
            "name" => $company?->name ?? ($user->first_name . " " . $user->last_name),
            "logoUrl" => $company?->logo_path ?? null,
            "tags" => $company?->tags ?? [],
            "description" => $company?->description ?? null,
            "email" => $company?->email ?? null,
            "phone" => $company?->phone ?? null,
            "website" => $company?->website ?? null,
            "street" => $company?->street ?? null,
            "buildingNumber" => $company?->building_number ?? null,
            "postalCode" => $company?->postal_code ?? null,
            "city" => $company?->city ?? null,
            "nip" => $company?->nip ?? null,
            "offers" => $company ? $company->offers()
                ->where("is_active", true)
                ->select("id", "title", "description", "spots")
                ->latest()
                ->get() : [],
        ];
    }
}