<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CompanyController extends Controller
{
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
}
