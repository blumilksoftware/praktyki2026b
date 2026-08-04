<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeleteOfferTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotDeleteOffer(): void
    {
        $offer = Offer::factory()->create();

        $response = $this->delete("/company/offers/{$offer->id}");

        $response->assertRedirect("/login");
    }

    public function testStudentCannotDeleteOffer(): void
    {
        $offer = Offer::factory()->create();
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($student)->delete("/company/offers/{$offer->id}");

        $response->assertForbidden();
    }

    public function testCompanyAdminCanDeleteTheirOwnOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);

        $response = $this->from("/company/offers")->actingAs($user)->delete("/company/offers/{$offer->id}");

        $response->assertRedirect("/company/offers");
        $this->assertSoftDeleted("offers", ["id" => $offer->id]);
    }

    public function testCompanyAdminCannotDeleteAnotherCompanysOffer(): void
    {
        $ownCompany = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($ownCompany);

        $otherCompany = Company::factory()->approved()->create();
        $offer = Offer::factory()->published()->create(["company_id" => $otherCompany->id]);

        $response = $this->actingAs($user)->delete("/company/offers/{$offer->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas("offers", ["id" => $offer->id, "deleted_at" => null]);
    }

    public function testDeletedOfferPreservesApplications(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);
        $offer = Offer::factory()->published()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);

        $this->actingAs($user)->delete("/company/offers/{$offer->id}");

        $this->assertDatabaseHas("applications", ["id" => $application->id]);
    }

    public function testDeletingOfferNotifiesApplicants(): void
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

        $this->actingAs($user)->delete("/company/offers/{$offer->id}");

        Mail::assertQueued(
            OfferUnavailableMail::class,
            fn(OfferUnavailableMail $mail): bool => $mail->hasTo($student->email) && $mail->reason === "deleted",
        );
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
