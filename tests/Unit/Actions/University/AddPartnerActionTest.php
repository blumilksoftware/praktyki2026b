<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\AddPartnerAction;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\Partnership;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function testUniversityCanAddCompanyAsPartner(): void
    {
        $university = University::factory()->create();
        $company = Company::factory()->create();

        $this->action->execute($university, $company);

        $this->assertDatabaseHas("partnerships", [
            "university_id" => $university->id,
            "company_id" => $company->id,
            "status" => PartnershipStatus::Active->value,
        ]);
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
