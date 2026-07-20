<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotPublishOffer(): void
    {
        $offer = Offer::factory()->draft()->create();

        $response = $this->patch("/company/offers/{$offer->id}/publish");

        $response->assertRedirect("/login");
    }

    public function testCompanyAdminCanPublishTheirOwnDraftOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->draft()->create(["company_id" => $company->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/publish");

        $response->assertRedirect("/company/dashboard");
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Published->value,
        ]);
    }

    public function testCompanyAdminCannotPublishAnotherCompanysOffer(): void
    {
        $ownCompany = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($ownCompany);

        $otherCompany = Company::factory()->approved()->create();
        $offer = Offer::factory()->draft()->create(["company_id" => $otherCompany->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/publish");

        $response->assertForbidden();
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Draft->value,
        ]);
    }

    public function testUnverifiedCompanyCannotPublishOffer(): void
    {
        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->draft()->create(["company_id" => $company->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/publish");

        $response->assertSessionHasErrors("status");
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Draft->value,
        ]);
    }

    public function testAlreadyPublishedOfferCannotBePublishedAgain(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/publish");

        $response->assertSessionHasErrors("status");
    }

    public function testStudentCannotPublishOffer(): void
    {
        $offer = Offer::factory()->draft()->create();
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($student)->patch("/company/offers/{$offer->id}/publish");

        $response->assertForbidden();
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
            "first_name" => null,
            "last_name" => null,
        ]);
    }
}
