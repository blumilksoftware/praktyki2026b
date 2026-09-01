<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Actions\Company\GetCompanyDashboardStats;
use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
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
        private readonly GetCompanyDashboardStats $getCompanyDashboardStats,
    ) {}

    public function index(Request $request): Response
    {
        $company = $request->user()->company;

        return inertia("Company/Dashboard", [
            "stats" => $this->getCompanyDashboardStats->execute($company),
        ]);
    }

    public function verificationPending(): Response
    {
        $user = Auth::user();

        return inertia("Auth/VerificationPending", [
            "user" => $user,
            "canCreateDraftOffer" => $user->status === UserStatus::Active
                && $user->company?->verification_status === VerificationStatus::Pending,
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

    public function partnership(): never
    {
        abort(404);
    }

    private function getCompanyProfileData(): array
    {
        return $this->buildCompanyProfileData->execute(Auth::user()->company, Auth::user());
    }
}
