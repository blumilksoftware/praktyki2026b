<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GeocodingRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function testStudentProfileUpdateIsThrottled(): void
    {
        $this->actingAs($this->student());

        $response = $this->spam(21, fn(): TestResponse => $this->patch(route("student.profile.update")));

        $this->assertThrottled($response);
    }

    public function testThrottledRequestWithoutAnEmailFieldIsFlashedAsAnError(): void
    {
        $this->actingAs($this->student());

        $response = $this->spam(21, fn(): TestResponse => $this->patch(route("student.profile.update")));

        $this->assertSame(
            __("auth.throttle", ["seconds" => $response->headers->get("Retry-After")]),
            session("error"),
        );
    }

    public function testOfferCreationIsThrottled(): void
    {
        $this->actingAs($this->companyAdmin());

        $response = $this->spam(31, fn(): TestResponse => $this->post(route("company.offers.store")));

        $this->assertThrottled($response);
    }

    public function testOfferUpdateIsThrottled(): void
    {
        $company = Company::factory()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);

        $this->actingAs($this->companyAdmin($company));

        $response = $this->spam(31, fn(): TestResponse => $this->patch(route("company.offers.update", $offer)));

        $this->assertThrottled($response);
    }

    public function testTheLimitIsNotSharedBetweenUsers(): void
    {
        $this->actingAs($this->student());
        $this->spam(21, fn(): TestResponse => $this->patch(route("student.profile.update")));

        $response = $this->actingAs($this->student())->patch(route("student.profile.update"));

        $response->assertHeaderMissing("Retry-After");
    }

    private function student(): User
    {
        return User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
    }

    private function companyAdmin(?Company $company = null): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => ($company ?? Company::factory()->create())->id,
        ]);
    }

    private function spam(int $times, callable $request): TestResponse
    {
        $response = null;

        for ($attempt = 0; $attempt < $times; $attempt++) {
            $response = $request();
        }

        return $response;
    }

    private function assertThrottled(TestResponse $response): void
    {
        $response->assertRedirect();
        $response->assertHeader("Retry-After");
        $this->assertGreaterThan(0, (int)$response->headers->get("Retry-After"));
    }
}
