<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UniversityDashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(["message" => "Welcome"]);
    }
}
