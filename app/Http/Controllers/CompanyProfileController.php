<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Company\BuildCompanyProfileData;
use App\Enums\UserRole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function __construct(
        private readonly BuildCompanyProfileData $buildCompanyProfileData,
    ) {}

    public function show(string $company): Response
    {
        $companyQuery = Auth::user()?->role === UserRole::SuperAdmin ? Company::query() : Company::verified();

        $foundCompany = $companyQuery->findOrFail($company);

        return inertia("Company/PublicProfile", [
            "company" => $this->buildCompanyProfileData->execute($foundCompany, Auth::user()),
        ]);
    }
}
