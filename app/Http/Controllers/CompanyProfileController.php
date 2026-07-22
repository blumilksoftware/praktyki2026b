<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Company\BuildCompanyProfileData;
use App\Enums\VerificationStatus;
use App\Models\Company;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function __construct(
        private readonly BuildCompanyProfileData $buildCompanyProfileData,
    ) {}

    public function show(Company $company): Response
    {
        abort_unless($company->verification_status === VerificationStatus::Verified, 404);

        return inertia("Company/PublicProfile", [
            "company" => $this->buildCompanyProfileData->execute($company),
        ]);
    }
}
