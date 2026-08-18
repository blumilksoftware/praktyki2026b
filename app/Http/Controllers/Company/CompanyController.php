<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\BuildCompanyProfileData;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Enums\OfferStatus;
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

        $offerStats = $company->offers()
            ->selectRaw(
                "count(*) as total, " .
                "sum(case when status = ? then 1 else 0 end) as published, " .
                "sum(case when status = ? then 1 else 0 end) as draft",
                [OfferStatus::Published->value, OfferStatus::Draft->value],
            )
            ->first();

        $stats = [
            "total_offers" => (int)$offerStats->total,
            "published_offers" => (int)$offerStats->published,
            "draft_offers" => (int)$offerStats->draft,
            "applications_count" => $company->applications()->count(),
            "unread_notifications_count" => $request->user()->unreadNotifications()->count(),
        ];

        return inertia("Company/Dashboard", [
            "offers" => $offers,
            "stats" => $stats,
            "sort" => $request->string("sort", "created_at")->toString(),
            "direction" => $request->string("direction", "desc")->toString(),
            "search" => $request->string("search")->toString(),
            "status" => $request->string("status")->toString(),
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

    private function getCompanyProfileData(): array
    {
        return $this->buildCompanyProfileData->execute(Auth::user()->company);
    }

    public function partnership(): never
    {
        abort(404);
    }
}
