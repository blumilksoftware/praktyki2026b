<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\RemovePartnerAction;
use App\Models\Company;
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

    public function testCompanyCanCancelItsOwnPendingRequest(): void
    {
        $company = Company::factory()->create(["name" => "Acme Corp"]);
        $university = University::factory()->create();
        $universityUser = User::factory()->create(["organization_id" => $university->id]);

        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        Notification::assertSentTo(
            $universityUser,
            fn(PartnershipCancelledNotification $notification): bool => $notification->toArray($universityUser)["canceller_name"] === "Acme Corp",
        );
    }

    public function testCompanyCanDeclineIncomingRequest(): void
    {
        $company = Company::factory()->create(["name" => "Acme Corp"]);
        $university = University::factory()->create();
        $universityUser = User::factory()->create(["organization_id" => $university->id]);

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        Notification::assertSentTo(
            $universityUser,
            fn(PartnershipDeclinedNotification $notification): bool => $notification->toArray($universityUser)["decliner_name"] === "Acme Corp",
        );
    }

    public function testCompanyCanEndActivePartnership(): void
    {
        $company = Company::factory()->create(["name" => "Acme Corp"]);
        $university = University::factory()->create();
        $universityUser = User::factory()->create(["organization_id" => $university->id]);

        Partnership::factory()->active()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseMissing("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        Notification::assertSentTo(
            $universityUser,
            fn(PartnershipEndedNotification $notification): bool => $notification->toArray($universityUser)["ender_name"] === "Acme Corp",
        );
    }

    public function testRemovingNonExistentPartnershipIsANoop(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        $this->action->execute($company, $university);

        $this->assertDatabaseCount("partnerships", 0);
    }

    public function testRemovingDoesNotAffectPartnershipsOfOtherCompanies(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->active()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);
    }
}
