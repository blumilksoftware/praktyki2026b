<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\OfferStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\OfferUnavailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeactivateOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotDeactivateOffer(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->patch("/company/offers/{$offer->id}/deactivate");

        $response->assertRedirect("/login");
    }

    public function testCompanyAdminCanDeactivateTheirOwnOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);

        $response = $this->from("/company/offers")->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        $response->assertRedirect("/company/offers");
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Closed->value,
        ]);
    }

    public function testCompanyAdminCannotDeactivateAnotherCompanysOffer(): void
    {
        $ownCompany = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($ownCompany);

        $otherCompany = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $otherCompany->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        $response->assertForbidden();
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Published->value,
        ]);
    }

    public function testDeactivatedOfferPreservesApplications(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);

        $this->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        $this->assertDatabaseHas("applications", ["id" => $application->id]);
    }

    public function testDeactivatingOfferNotifiesApplicants(): void
    {
        Mail::fake();

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $student->id]);

        $this->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($student->email) && $mail->reason === "closed",
        );
    }

    public function testDeactivatingOfferCreatesInAppNotificationForApplicants(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $student->id]);

        $this->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        $this->assertDatabaseHas("notifications", [
            "notifiable_id" => $student->id,
            "type" => OfferUnavailableNotification::class,
        ]);
    }

    public function testExpiredOfferCannotBeDeactivatedAgain(): void
    {
        Mail::fake();

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->expired()->create(["company_id" => $company->id]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $student->id]);

        $response = $this->actingAs($user)->patch("/company/offers/{$offer->id}/deactivate");

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas("offers", [
            "id" => $offer->id,
            "status" => OfferStatus::Expired->value,
        ]);
        Mail::assertNotQueued(OfferUnavailableMail::class);
    }

    public function testStudentCannotDeactivateOffer(): void
    {
        $offer = Offer::factory()->create();
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($student)->patch("/company/offers/{$offer->id}/deactivate");

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
