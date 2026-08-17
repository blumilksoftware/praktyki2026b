<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\AcceptPartnershipAction;
use App\Actions\Company\GetUniversityFilterOptions;
use App\Actions\Company\RemovePartnerAction;
use App\Actions\Company\RequestPartnershipAction;
use App\Actions\Company\SearchUniversities;
use App\DTO\Company\SearchUniversitiesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchPartnerUniversitiesRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class UniversityController extends Controller
{
    public function __construct(
        private readonly SearchUniversities $searchUniversities,
        private readonly RequestPartnershipAction $requestPartnershipAction,
        private readonly RemovePartnerAction $removePartnerAction,
        private readonly AcceptPartnershipAction $acceptPartnershipAction,
        private readonly GetUniversityFilterOptions $getUniversityFilterOptions,
    ) {}

    public function index(SearchPartnerUniversitiesRequest $request): Response
    {
        $companyId = Auth::user()->organization_id;

        $universities = $this->searchUniversities->execute(
            SearchUniversitiesData::fromArray($request->getData()),
            $companyId,
        );

        $filterOptions = $this->getUniversityFilterOptions->execute();

        return inertia("Company/Universities/Index", [
            "universities" => $universities,
            "filters" => $request->validated(),
            "cityOptions" => $filterOptions["cities"],
        ]);
    }

    public function addPartner(University $university): RedirectResponse
    {
        $company = Auth::user()->company;

        $this->requestPartnershipAction->execute($company, $university);

        return back();
    }

    public function removePartner(University $university): RedirectResponse
    {
        $company = Auth::user()->company;

        $this->removePartnerAction->execute($company, $university);

        return back();
    }

    public function acceptPartner(University $university): RedirectResponse
    {
        $company = Auth::user()->company;

        $this->acceptPartnershipAction->execute($company, $university);

        return back();
    }
}
