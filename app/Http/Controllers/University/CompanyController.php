<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\University\AddPartnerAction;
use App\Actions\University\GetCompanyFilterOptions;
use App\Actions\University\RemovePartnerAction;
use App\Actions\University\SearchCompanies;
use App\DTO\University\SearchCompaniesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchCompaniesRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly SearchCompanies $searchCompanies,
        private readonly AddPartnerAction $addPartnerAction,
        private readonly RemovePartnerAction $removePartnerAction,
        private readonly GetCompanyFilterOptions $getCompanyFilterOptions,
    ) {}

    public function index(SearchCompaniesRequest $request): Response
    {
        $universityId = Auth::user()->organization_id;

        $companies = $this->searchCompanies->execute(
            SearchCompaniesData::fromArray($request->getData()),
            $universityId,
        );

        $filterOptions = $this->getCompanyFilterOptions->execute();

        return inertia("University/Companies/Index", [
            "companies" => $companies,
            "filters" => $request->validated(),
            "cityOptions" => $filterOptions["cities"],
            "tagOptions" => $filterOptions["tags"],
        ]);
    }

    public function addPartner(Company $company): RedirectResponse
    {
        $university = Auth::user()->universityOrganization;

        $this->addPartnerAction->execute($university, $company);

        return back();
    }

    public function removePartner(Company $company): RedirectResponse
    {
        $university = Auth::user()->universityOrganization;

        $this->removePartnerAction->execute($university, $company);

        return back();
    }
}
