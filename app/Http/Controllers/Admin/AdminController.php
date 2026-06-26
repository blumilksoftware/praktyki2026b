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

    public function applications(Request $request): Response
    {
        $statusFilter = $request->query("status", "all");

        if (!is_string($statusFilter) || !mb_check_encoding($statusFilter, "UTF-8")) {
            $statusFilter = "all";
        }

        $searchQuery = $request->query("search", "");

        if (!is_string($searchQuery) || !mb_check_encoding($searchQuery, "UTF-8")) {
            $searchQuery = "";
        }

        $allowedCompanySorts = ["name", "email", "city", "created_at", "verification_status"];
        $allowedUniversitySorts = ["name", "domain", "email", "created_at", "verification_status"];
        $sortDir = $request->query("sort_dir", "asc") === "desc" ? "desc" : "asc";

        $companySortKey = $request->query("sort_key", "created_at");

        if (!in_array($companySortKey, $allowedCompanySorts, true)) {
            $companySortKey = "created_at";
        }

        $universitySortKey = $request->query("sort_key", "created_at");

        if (!in_array($universitySortKey, $allowedUniversitySorts, true)) {
            $universitySortKey = "created_at";
        }

        $companyStats = [
            "pending" => Company::where("verification_status", VerificationStatus::Pending)->count(),
            "verified" => Company::where("verification_status", VerificationStatus::Verified)->count(),
            "rejected" => Company::where("verification_status", VerificationStatus::Rejected)->count(),
        ];

        $universityStats = [
            "pending" => University::where("verification_status", VerificationStatus::Pending)->count(),
            "verified" => University::where("verification_status", VerificationStatus::Verified)->count(),
            "rejected" => University::where("verification_status", VerificationStatus::Rejected)->count(),
        ];

        $companiesQuery = Company::query();

        if ($statusFilter !== "all") {
            $companiesQuery->where("verification_status", $statusFilter);
        }

        if ($searchQuery) {
            $companiesQuery->where(function ($q) use ($searchQuery): void {
                $q->where("name", "like", "%{$searchQuery}%")
                    ->orWhere("email", "like", "%{$searchQuery}%");
            });
        }

        $companiesQuery->orderBy($companySortKey, $sortDir); 
        $companies = $companiesQuery->paginate(20, ["*"], "companies_page")->appends([
            "status" => $statusFilter,
            "search" => $searchQuery,
            "sort_key" => $companySortKey,
            "sort_dir" => $sortDir,
        ]);

        $universitiesQuery = University::query();

        if ($statusFilter !== "all") {
            $universitiesQuery->where("verification_status", $statusFilter);
        }

        if ($searchQuery) {
            $universitiesQuery->where(function ($q) use ($searchQuery): void {
                $q->where("name", "like", "%{$searchQuery}%")
                    ->orWhere("email", "like", "%{$searchQuery}%");
            });
        }
        $universitiesQuery->orderBy($universitySortKey, $sortDir);
        $universities = $universitiesQuery->paginate(20, ["*"], "universities_page")->appends([
            "status" => $statusFilter,
            "search" => $searchQuery,
            "sort_key" => $universitySortKey,
            "sort_dir" => $sortDir,
        ]);

        return inertia("Admin/Applications", [
            "companies" => $companies,
            "universities" => $universities,
            "companyStats" => $companyStats,
            "universityStats" => $universityStats,
            "filters" => [
                "status" => $statusFilter,
                "search" => $searchQuery,
            ],
            "meta" => [
                "title" => "Admin Applications",
            ],
        ]);
    }

    public function acceptCompanyVerification(Company $company, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($company, __("emails.verification.already_verified_company"), "admin.applications");

        if ($redirect !== null) {
            return $redirect;
        }

        $this->verifyAction->verify($company, auth()->user());

        return redirect()->route("admin.applications", [
            "companies_page" => $request->input("companies_page", 1),
            "universities_page" => $request->input("universities_page", 1),
            "sort_key" => $request->input("sort_key", "created_at"),
            "sort_dir" => $request->input("sort_dir", "asc"),
        ]);
    }

    public function acceptUniversityVerification(University $university, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($university, __("emails.verification.already_verified_university"), "admin.applications");

        if ($redirect !== null) {
            return $redirect;
        }

        $this->verifyAction->verify($university, auth()->user());

        return redirect()->route("admin.applications", [
            "companies_page" => $request->input("companies_page", 1),
            "universities_page" => $request->input("universities_page", 1),
            "sort_key" => $request->input("sort_key", "created_at"),
            "sort_dir" => $request->input("sort_dir", "asc"),
        ]);
    }

    public function rejectCompanyVerification(Company $company, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($company, __("emails.verification.already_rejected_company"), "admin.applications");

        if ($redirect !== null) {
            return $redirect;
        }

        $validated = $request->validate(["rejection_reason" => "required|string"]);
        $this->verifyAction->reject($company, $validated["rejection_reason"], auth()->user());

        return redirect()->route("admin.applications", [
            "companies_page" => $request->input("companies_page", 1),
            "universities_page" => $request->input("universities_page", 1),
            "sort_key" => $request->input("sort_key", "created_at"),
            "sort_dir" => $request->input("sort_dir", "asc"),
        ]);
    }

    public function rejectUniversityVerification(University $university, Request $request): RedirectResponse
    {
        $redirect = $this->checkIfVerifiedWithRedirect($university, __("emails.verification.already_rejected_university"), "admin.applications");

        if ($redirect !== null) {
            return $redirect;
        }

        $validated = $request->validate(["rejection_reason" => "required|string"]);
        $this->verifyAction->reject($university, $validated["rejection_reason"], auth()->user());

        return redirect()->route("admin.applications", [
            "companies_page" => $request->input("companies_page", 1),
            "universities_page" => $request->input("universities_page", 1),
            "sort_key" => $request->input("sort_key", "created_at"),
            "sort_dir" => $request->input("sort_dir", "asc"),
        ]);
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
