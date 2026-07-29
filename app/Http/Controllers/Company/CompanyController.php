<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly UpdateCompanyProfile $updateCompanyProfile,
        private readonly BuildCompanyProfileData $buildCompanyProfileData,
        private readonly GetOffersSummary $getOffersSummary,
    ) {}

    public function index(Request $request): Response
    {
        $company = Auth::user()->company;

        return inertia("Company/Dashboard", [
            "offers" => $this->getOffersSummary->execute(
                $company,
                $request->query("sort", "created_at"),
                $request->query("direction", "desc"),
            ),
            "sort" => $request->query("sort", "created_at"),
            "direction" => $request->query("direction", "desc"),
        ]);
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
        return $this->buildCompanyProfileData->execute(Auth::user()->company);
    }
}
