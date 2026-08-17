<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\RemovePartnerAction;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use App\Notifications\PartnershipCancelledNotification;
use App\Notifications\PartnershipDeclinedNotification;
use App\Notifications\PartnershipEndedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RemovePartnerActionTest extends TestCase
{
    use RefreshDatabase;

    private RemovePartnerAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RemovePartnerAction();
        Notification::fake();
    }

    public function testUniversityCanRemovePartnership(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testRemovingNonExistentPartnershipIsANoop(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        $this->assertDatabaseMissing("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }

    public function testRemovingPartnershipDoesNotAffectExistingApplications(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);
        Partnership::factory()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("applications", [
            "id" => $application->id,
        ]);
    }

    public function testCancellingOwnPendingRequestNotifiesCompany(): void
    {
        $university = University::factory()->create(["name" => "Politechnika Testowa"]);
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(["organization_id" => $company->id]);
        Partnership::factory()->pending()->requestedByUniversity()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        Notification::assertSentTo(
            $companyUser,
            fn(PartnershipCancelledNotification $notification): bool => $notification->toArray($companyUser)["canceller_name"] === "Politechnika Testowa",
        );
    }

    public function testDecliningIncomingRequestNotifiesCompany(): void
    {
        $university = University::factory()->create(["name" => "Politechnika Testowa"]);
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(["organization_id" => $company->id]);
        Partnership::factory()->pending()->requestedByCompany()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        Notification::assertSentTo(
            $companyUser,
            fn(PartnershipDeclinedNotification $notification): bool => $notification->toArray($companyUser)["decliner_name"] === "Politechnika Testowa",
        );
    }

    public function testEndingActivePartnershipNotifiesCompany(): void
    {
        $university = University::factory()->create(["name" => "Politechnika Testowa"]);
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(["organization_id" => $company->id]);
        Partnership::factory()->active()->requestedByCompany()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        Notification::assertSentTo(
            $companyUser,
            fn(PartnershipEndedNotification $notification): bool => $notification->toArray($companyUser)["ender_name"] === "Politechnika Testowa",
        );
    }
}
