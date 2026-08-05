<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Organization;

use App\Actions\Organization\RemoveTeamMember;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RemoveTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    private RemoveTeamMember $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RemoveTeamMember();
    }

    public function testItDeletesMember(): void
    {
        $company = Company::factory()->approved()->create();
        User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->action->execute($member);

        $this->assertModelMissing($member);
    }

    public function testItDeletesAdminWhenAnotherAdminRemains(): void
    {
        $company = Company::factory()->approved()->create();
        User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->action->execute($admin);

        $this->assertModelMissing($admin);
    }

    public function testItRejectsRemovingTheLastCompanyAdmin(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->expectException(ValidationException::class);

        try {
            $this->action->execute($admin);
        } finally {
            $this->assertModelExists($admin);
        }
    }

    public function testItRejectsRemovingTheLastUniversityAdmin(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $this->expectException(ValidationException::class);

        $this->action->execute($admin);
    }

    public function testItIgnoresAdminsOfOtherOrganizationsWhenCountingAdmins(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        User::factory()->companyAdmin()->create(["organization_id" => $otherCompany->id]);

        $this->expectException(ValidationException::class);

        $this->action->execute($admin);
    }
}
