<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly UpdateCompanyProfile $updateCompanyProfile,
    ) {}

    public function index(): Response
    {
        return inertia("Company/Dashboard");
    }

    public function profile(): Response
    {
        return inertia("Company/Profile", [
            "user" => Auth::user(),
        ]);
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;
        $data = UpdateCompanyProfileData::fromArray($request->getData());

        $this->updateCompanyProfile->execute($company, $data);

        return redirect()->route("company.profile");
    }
}
