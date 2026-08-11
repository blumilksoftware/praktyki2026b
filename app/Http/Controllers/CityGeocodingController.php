<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MapboxGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CityGeocodingController extends Controller
{
    public function __construct(
        private readonly MapboxGeocodingService $geocodingService,
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            "query" => ["required", "string", "min:3", "max:100"],
        ]);

        try {
            $cities = $this->geocodingService->suggestCities($request->string("query")->toString());
        } catch (RuntimeException) {
            return response()->json(
                ["message" => __("validation.city_suggestions_unavailable")],
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        return response()->json($cities);
    }
}
