<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\MapboxGeocodingService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MapboxGeocodingServiceTest extends TestCase
{
    public function testGeocodeReturnsCoordinatesForValidCity(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response([
                "features" => [
                    ["center" => [21.0122, 52.2297]],
                ],
            ]),
        ]);

        $service = new MapboxGeocodingService();
        $coordinates = $service->geocode("Warszawa");

        $this->assertEqualsWithDelta(52.2297, $coordinates["latitude"], 0.0001);
        $this->assertEqualsWithDelta(21.0122, $coordinates["longitude"], 0.0001);
    }

    public function testGeocodeThrowsExceptionWhenNoFeaturesFound(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(["features" => []]),
        ]);

        $service = new MapboxGeocodingService();

        $this->expectException(RuntimeException::class);
        $service->geocode("Nonexistentville");
    }

    public function testGeocodeThrowsExceptionWhenRequestFails(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(null, 500),
        ]);

        $service = new MapboxGeocodingService();

        $this->expectException(RuntimeException::class);
        $service->geocode("Warszawa");
    }
}
