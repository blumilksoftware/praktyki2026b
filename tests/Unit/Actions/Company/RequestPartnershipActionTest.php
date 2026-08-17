<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\RequestPartnershipAction;
use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Mail\Partnership\PartnershipRequestedMail;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use App\Notifications\PartnershipRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequestPartnershipActionTest extends TestCase
{
    use RefreshDatabase;

    private RequestPartnershipAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RequestPartnershipAction();
        Mail::fake();
        Notification::fake();
    }

    public function testCompanyRequestIsPendingAndMarkedAsInitiatedByCompany(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        $this->action->execute($company, $university);

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
            "status" => PartnershipStatus::Pending->value,
            "requested_by" => PartnershipInitiator::Company->value,
        ]);
    }

    public function testItNotifiesUniversityUsersAboutTheRequest(): void
    {
        $company = Company::factory()->create(["name" => "Testowa Firma"]);
        $university = University::factory()->create();
        $universityUser = User::factory()->create(["organization_id" => $university->id]);

        $this->action->execute($company, $university);

        Notification::assertSentTo(
            $universityUser,
            fn(PartnershipRequestedNotification $notification): bool => $notification->toArray($universityUser)["proposer_name"] === "Testowa Firma",
        );
    }

    public function testItQueuesHeadsUpEmailToTheUniversity(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        $this->action->execute($company, $university);

        Mail::assertQueued(PartnershipRequestedMail::class);
    }

    public function testRequestingSameUniversityTwiceIsRejected(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        $this->action->execute($company, $university);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.already_university_partner"));

        $this->action->execute($company, $university);
    }

    public function testRequestingUniversityAlreadyPartneredWithAnotherCompanyIsAllowed(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);
    }
}
