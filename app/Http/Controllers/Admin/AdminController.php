<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\University;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $companiesNeedingVerification = Company::needingVerification()->oldest()->get();
        $universitiesNeedingVerification = University::needingVerification()->oldest()->get();

        return inertia("Admin/Dashboard", [
            "companiesNeedingVerification" => $companiesNeedingVerification,
            "universitiesNeedingVerification" => $universitiesNeedingVerification,
        ]);
    }
}
