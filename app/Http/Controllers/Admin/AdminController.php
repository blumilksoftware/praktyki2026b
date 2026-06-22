<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\VerifyEntityAction;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        private readonly VerifyEntityAction $verifyAction,
    ) {}

    public function index(): Response
    {
        $companiesNeedingVerification = Company::needingVerification()->oldest()->get();
        $universitiesNeedingVerification = University::needingVerification()->oldest()->get();

        return inertia("Admin/Dashboard", [
            "companiesNeedingVerification" => $companiesNeedingVerification,
            "universitiesNeedingVerification" => $universitiesNeedingVerification,
            "meta" => [
                "title" => "Admin Dashboard",
            ],
        ]);
    }

    public function applications(): Response
    {
        return inertia("Admin/Applications", [
            "meta" => [
                "title" => "Admin Applications",
            ],
        ]);
    }

    public function acceptCompanyVerification(Company $company): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($company, __("emails.verification.already_verified_company"), "admin.dashboard");

        if ($redirect !== null) {
            return $redirect;
        }

        $this->verifyAction->verify($company, auth()->user());

        return redirect()->route("admin.dashboard");
    }

    public function rejectCompanyVerification(Company $company, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($company, __("emails.verification.already_rejected_company"), "admin.dashboard");

        if ($redirect !== null) {
            return $redirect;
        }

        $rejectionReason = $request->input("rejection_reason");
        $this->verifyAction->reject($company, $rejectionReason, auth()->user());

        return redirect()->route("admin.dashboard");
    }

    public function acceptUniversityVerification(University $university): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($university, __("emails.verification.already_verified_university"), "admin.dashboard");

        if ($redirect !== null) {
            return $redirect;
        }

        $this->verifyAction->verify($university, auth()->user());

        return redirect()->route("admin.dashboard");
    }

    public function rejectUniversityVerification(University $university, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($university, __("emails.verification.already_rejected_university"), "admin.dashboard");

        if ($redirect !== null) {
            return $redirect;
        }

        $rejectionReason = $request->input("rejection_reason");

        $this->verifyAction->reject($university, $rejectionReason, auth()->user());

        return redirect()->route("admin.dashboard");
    }

    private function checkIfVerifiedWithRedirect(University|Company $entity, string $message, string $route): ?RedirectResponse
    {
        if ($entity->verification_status === VerificationStatus::Verified ||
            $entity->verification_status === VerificationStatus::Rejected) {
            return redirect()->route($route)->withErrors($message);
        }

        return null;
    }
}
