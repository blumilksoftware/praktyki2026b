<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\MapboxGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityGeocodingController extends Controller
{
    public function __construct(
        private readonly MapboxGeocodingService $geocodingService,
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            "query" => ["required", "string", "min:2", "max:100"],
        ]);

        return response()->json(
            $this->geocodingService->suggestCities($request->string("query")->toString()),
        );
    }
}
