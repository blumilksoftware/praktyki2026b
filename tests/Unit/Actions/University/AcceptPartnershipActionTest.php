<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\AcceptPartnershipAction;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use App\Models\User;
use App\Notifications\PartnershipAcceptedNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcceptPartnershipActionTest extends TestCase
{
    use RefreshDatabase;

    private AcceptPartnershipAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AcceptPartnershipAction();
        Notification::fake();
    }

    public function testUniversityCanAcceptRequestSentByCompany(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        Partnership::factory()->pending()->requestedByCompany()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
            "status" => PartnershipStatus::Active->value,
        ]);
    }

    public function testItNotifiesCompanyUsersThatTheirRequestWasAccepted(): void
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
            fn(PartnershipAcceptedNotification $notification): bool => $notification->toArray($companyUser)["acceptor_name"] === "Politechnika Testowa",
        );
    }

    public function testUniversityCannotAcceptItsOwnRequest(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.cannot_accept_own_partnership_request"));

        $this->action->execute($university, $company);
    }

    public function testAlreadyActivePartnershipCannotBeAcceptedAgain(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        Partnership::factory()->active()->requestedByCompany()->create([
            "university_id" => $university->id,
            "company_id" => $company->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.partnership_not_pending"));

        $this->action->execute($university, $company);
    }

    public function testAcceptingNonExistentPartnershipFails(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->action->execute($university, $company);
    }

    public function testUniversityCannotAcceptPartnershipBelongingToAnotherUniversity(): void
    {
        $university = University::factory()->create();
        $otherUniversity = University::factory()->create();
        $company = Company::factory()->create();

        Partnership::factory()->pending()->requestedByCompany()->create([
            "university_id" => $otherUniversity->id,
            "company_id" => $company->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->action->execute($university, $company);
    }
}
