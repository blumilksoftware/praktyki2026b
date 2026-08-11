<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoding;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CitySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanFetchCitySuggestions(): void
    {
        $this->fakeSuggestions();

        $response = $this->getJson("/geocoding/cities?query=warszawa");

        $response->assertOk();
        $response->assertExactJson([
            [
                "name" => "Warszawa",
                "fullName" => "Warszawa, województwo mazowieckie, Polska",
                "latitude" => 52.2297,
                "longitude" => 21.0122,
            ],
        ]);
    }

    public function testStudentCanFetchCitySuggestions(): void
    {
        $this->fakeSuggestions();

        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($student)->getJson("/geocoding/cities?query=warszawa");

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function testQueryShorterThanThreeCharactersIsRejected(): void
    {
        $this->fakeSuggestions();

        $response = $this->getJson("/geocoding/cities?query=wa");

        $response->assertUnprocessable();
        $response->assertInvalid("query");
        Http::assertNothingSent();
    }

    public function testFailedUpstreamRequestReturnsErrorInsteadOfEmptyList(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response(null, 401),
        ]);

        $response = $this->getJson("/geocoding/cities?query=warszawa");

        $response->assertServiceUnavailable();
        $response->assertJson([
            "message" => __("validation.city_suggestions_unavailable"),
        ]);
    }

    private function fakeSuggestions(): void
    {
        Http::fake([
            "api.mapbox.com/*" => Http::response([
                "features" => [
                    [
                        "text" => "Warszawa",
                        "place_name" => "Warszawa, województwo mazowieckie, Polska",
                        "center" => [21.0122, 52.2297],
                    ],
                ],
            ]),
        ]);
    }
}
