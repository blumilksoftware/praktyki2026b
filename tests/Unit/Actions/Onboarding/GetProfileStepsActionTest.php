<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Onboarding;

use App\Actions\Onboarding\GetProfileStepsAction;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProfileStepsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetProfileStepsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetProfileStepsAction();
    }

    public function testStudentGets2Steps(): void
    {
        $user = User::factory()->create(["role" => UserRole::Student]);

        $this->assertCount(2, $this->action->execute($user));
    }

    public function testStudentStepKeys(): void
    {
        $user = User::factory()->create(["role" => UserRole::Student]);

        $keys = array_map(fn($s) => $s->key, $this->action->execute($user));

        $this->assertEquals(["university", "cv"], $keys);
    }

    public function testStudentFilledFieldsAreCompleted(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "university" => null,
            "cv_path" => "cvs/test.pdf",
        ]);

        $steps = $this->action->execute($user);

        $this->assertFalse($steps[0]->completed);
        $this->assertTrue($steps[1]->completed);
    }

    public function testCompanyAdminGets3Steps(): void
    {
        $user = User::factory()->create(["role" => UserRole::CompanyAdmin]);

        $this->assertCount(3, $this->action->execute($user));
    }

    public function testCompanyAdminFilledFieldsAreCompleted(): void
    {
        $company = Company::factory()->create([
            "logo_path" => null,
            "description" => "A great company",
            "tags" => ["php"],
        ]);
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "organization_id" => $company->id,
        ]);

        $steps = $this->action->execute($user);

        $this->assertFalse($steps[0]->completed);
        $this->assertTrue($steps[1]->completed);
        $this->assertTrue($steps[2]->completed);
    }

    public function testUniversityAdminGets2Steps(): void
    {
        $user = User::factory()->create(["role" => UserRole::UniversityAdmin]);

        $this->assertCount(2, $this->action->execute($user));
    }

    public function testUniversityAdminFilledFieldsAreCompleted(): void
    {
        $university = University::factory()->create([
            "logo_path" => "logos/uni.png",
            "external_form_url" => null,
        ]);
        $user = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "organization_id" => $university->id,
        ]);

        $steps = $this->action->execute($user);

        $this->assertTrue($steps[0]->completed);
        $this->assertFalse($steps[1]->completed);
    }

    public function testSuperAdminGetsNoSteps(): void
    {
        $user = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->assertCount(0, $this->action->execute($user));
    }

    public function testIsCompleteReturnsTrueForSuperAdmin(): void
    {
        $user = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->assertTrue($this->action->isComplete($user));
    }

    public function testIsCompleteReturnsFalseWhenStepsIncomplete(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "cv_path" => null,
        ]);

        $this->assertFalse($this->action->isComplete($user));
    }

    public function testIsCompleteReturnsTrueWhenAllStepsDone(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "university" => "Example university",
            "cv_path" => "cvs/test.pdf",
        ]);

        $this->assertTrue($this->action->isComplete($user));
    }
}
