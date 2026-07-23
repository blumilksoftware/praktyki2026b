<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\University\SearchCompanies;
use App\DTO\University\SearchCompaniesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchCompaniesRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly SearchCompanies $searchCompanies,
    ) {}

    public function index(SearchCompaniesRequest $request): Response
    {
        $universityId = Auth::user()->organization_id;

        $companies = $this->searchCompanies->execute(
            SearchCompaniesData::fromArray($request->getData()),
            $universityId,
        );

        return inertia("University/Companies/Index", [
            "companies" => $companies,
            "filters" => $request->validated(),
        ]);
    }
}
