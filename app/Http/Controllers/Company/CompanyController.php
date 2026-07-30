<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Enums\OfferStatus;
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
        $company = $request->user()->company;

        $offers = $this->getOffersSummary->execute(
            $company,
            $request->string("sort", "created_at")->toString(),
            $request->string("direction", "desc")->toString(),
            OfferStatus::tryFrom($request->string("status")->toString()),
            $request->string("search")->toString() ?: null,
        );

        return inertia("Company/Dashboard", [
            "offers" => $offers,
            "sort" => $request->string("sort", "created_at")->toString(),
            "direction" => $request->string("direction", "desc")->toString(),
            "search" => $request->string("search")->toString(),
            "status" => $request->string("status")->toString(),
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
