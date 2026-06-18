<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CompanyDashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(["message" => "Welcome"]);
    }
}
