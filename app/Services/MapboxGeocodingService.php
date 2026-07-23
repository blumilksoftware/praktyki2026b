<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MapboxGeocodingService
{
    /**
     * @return array{latitude: float, longitude: float}
     */
    public function geocode(string $city): array
    {
        return Cache::rememberForever(
            "mapbox:geocode:" . mb_strtolower(trim($city)),
            function () use ($city): array {
                $response = Http::timeout(5)->get(
                    "https://api.mapbox.com/geocoding/v5/mapbox.places/" . rawurlencode($city) . ".json",
                    [
                        "access_token" => config("services.mapbox.access_token"),
                        "limit" => 1,
                    ],
                );

                $coordinates = $response->json("features.0.center");

                if ($response->failed() || !is_array($coordinates) || count($coordinates) !== 2) {
                    throw new RuntimeException("Failed to geocode city: {$city}");
                }

                return [
                    "latitude" => (float)$coordinates[1],
                    "longitude" => (float)$coordinates[0],
                ];
            },
        );
    }

    /**
     * @return list<array{name: string, fullName: string, latitude: float, longitude: float}>
     */
    public function suggestCities(string $query): array
    {
        $normalized = mb_strtolower(trim($query));

        if (mb_strlen($normalized) < 2) {
            return [];
        }

        return Cache::remember(
            "mapbox:suggest:{$normalized}",
            now()->addHours(6),
            function () use ($query): array {
                $response = Http::timeout(5)->get(
                    "https://api.mapbox.com/geocoding/v5/mapbox.places/" . rawurlencode($query) . ".json",
                    [
                        "access_token" => config("services.mapbox.access_token"),
                        "types" => "place",
                        "language" => "pl",
                        "country" => config("services.mapbox.country", "pl"),
                        "limit" => 5,
                    ],
                );

                if ($response->failed()) {
                    return [];
                }

                $features = $response->json("features") ?? [];

                return array_map(
                    static fn(array $feature): array => [
                        "name" => $feature["text"],
                        "fullName" => $feature["place_name"],
                        "latitude" => (float)$feature["center"][1],
                        "longitude" => (float)$feature["center"][0],
                    ],
                    $features,
                );
            },
        );
    }
}
