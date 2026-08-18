<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\AcceptPartnershipAction;
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

    public function testCompanyCanAcceptRequestSentByUniversity(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        $this->assertDatabaseHas("partnerships", [
            "company_id" => $company->id,
            "university_id" => $university->id,
            "status" => PartnershipStatus::Active->value,
        ]);
    }

    public function testItNotifiesUniversityUsersThatTheirRequestWasAccepted(): void
    {
        $company = Company::factory()->create(["name" => "Acme Corp"]);
        $university = University::factory()->create();
        $universityUser = User::factory()->create(["organization_id" => $university->id]);

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->action->execute($company, $university);

        Notification::assertSentTo(
            $universityUser,
            fn(PartnershipAcceptedNotification $notification): bool => $notification->toArray($universityUser)["acceptor_name"] === "Acme Corp",
        );
    }

    public function testCompanyCannotAcceptItsOwnRequest(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->pending()->requestedByCompany()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.cannot_accept_own_partnership_request"));

        $this->action->execute($company, $university);
    }

    public function testAlreadyActivePartnershipCannotBeAcceptedAgain(): void
    {
        $company = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->active()->requestedByUniversity()->create([
            "company_id" => $company->id,
            "university_id" => $university->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__("validation.partnership_not_pending"));

        $this->action->execute($company, $university);
    }

    public function testCompanyCannotAcceptPartnershipBelongingToAnotherCompany(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $university = University::factory()->create();

        Partnership::factory()->pending()->requestedByUniversity()->create([
            "company_id" => $otherCompany->id,
            "university_id" => $university->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->action->execute($company, $university);
    }
}
