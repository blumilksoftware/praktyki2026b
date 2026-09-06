<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Actions\Company\GetCompanyDashboardStats;
use App\Actions\Company\UpdateCompanyProfile;
use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Account\ChangePassword;
use App\Actions\Account\RequestEmailChange;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateCompanyProfileRequest;
use App\Models\IndustryTag;
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
        private readonly ChangePassword $changePassword,
        private readonly RequestEmailChange $requestEmailChange,
        private readonly RemoveTeamMember $removeTeamMember,
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
            "availableTags" => IndustryTag::query()->orderBy("name")->pluck("name"),
        ]);
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;

        $data = UpdateCompanyProfileData::fromArray($request->getData());

        $this->updateCompanyProfile->execute($company, $data);

        return redirect()->route("company.profile");
    }

    public function settings(): Response
    {
        $user = Auth::user();

        return inertia("Company/Settings", [
            "email" => $user->email,
            "emailVerifiedAt" => $user->email_verified_at?->toIso8601String(),
            "pendingEmail" => $user->pending_email,
        ]);
    }

    private function getCompanyProfileData(): array
    {
        return $this->buildCompanyProfileData->execute(Auth::user()->company, Auth::user());
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->changePassword->execute(Auth::user(), $request->string("password")->toString());

        return back();
    }

    public function changeEmail(ChangeEmailRequest $request): RedirectResponse
    {
        $this->requestEmailChange->execute(Auth::user(), $request->string("email")->toString());

        return back();
    }

    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->removeTeamMember->execute($user);

        $user->setRememberToken(null);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/");
    }
}
