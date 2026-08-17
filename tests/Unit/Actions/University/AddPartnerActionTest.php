<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\AddPartnerAction;
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

class AddPartnerActionTest extends TestCase
{
    use RefreshDatabase;

    private AddPartnerAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AddPartnerAction();
        Mail::fake();
        Notification::fake();
    }

    public function testUniversityRequestIsPendingAndMarkedAsInitiatedByUniversity(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
            "status" => PartnershipStatus::Pending->value,
            "requested_by" => PartnershipInitiator::University->value,
        ]);
    }

    public function testItNotifiesCompanyUsersAboutTheRequest(): void
    {
        $university = University::factory()->create(["name" => "Politechnika Testowa"]);
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(["organization_id" => $company->id]);

        $this->action->execute($university, $company);

        Notification::assertSentTo(
            $companyUser,
            fn(PartnershipRequestedNotification $notification): bool => $notification->toArray($companyUser)["proposer_name"] === "Politechnika Testowa",
        );
    }

    public function testItQueuesHeadsUpEmailToTheCompany(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        Mail::assertQueued(PartnershipRequestedMail::class);
    }

    public function testAddingSameCompanyTwiceIsRejected(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.already_partner"));

        $this->action->execute($university, $company);
    }

    public function testAddingCompanyAlreadyPartnerOfAnotherUniversityIsAllowed(): void
    {
        $university = University::factory()->create();
        $otherUniversity = University::factory()->create();
        $company = Company::factory()->create();

        Partnership::factory()->create([
            "company_id" => $company->id,
            "university_id" => $otherUniversity->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);
    }
}
