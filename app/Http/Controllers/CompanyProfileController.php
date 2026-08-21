<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Company\BuildCompanyProfileData;
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
        $verifiedCompany = Company::verified()->findOrFail($company);

        return inertia("Company/PublicProfile", [
            "company" => $this->buildCompanyProfileData->execute($verifiedCompany, Auth::user()),
        ]);
    }
}
