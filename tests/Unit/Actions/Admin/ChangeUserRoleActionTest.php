<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Admin;

use App\Actions\Admin\ChangeUserRoleAction;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ChangeUserRoleActionTest extends TestCase
{
    use RefreshDatabase;

    private ChangeUserRoleAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ChangeUserRoleAction();
    }

    public function testItChangesTheTargetUsersRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->action->execute($admin, $target, UserRole::CompanyAdmin);

        $this->assertEquals(UserRole::CompanyAdmin, $target->fresh()->role);
    }

    public function testItLogsWhoChangedWhat(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);

        $this->action->execute($admin, $target, UserRole::CompanyAdmin);

        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $target->id,
            "causer_id" => $admin->id,
            "description" => "user_role_changed",
        ]);
    }

    public function testItAssignsOrganizationWhenRoleRequiresOne(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);
        $company = Company::factory()->create();

        $this->action->execute($admin, $target, UserRole::CompanyAdmin, $company->id);

        $this->assertEquals($company->id, $target->fresh()->organization_id);
    }

    public function testItClearsOrganizationWhenRoleNoLongerNeedsOne(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $company = Company::factory()->create();
        $target = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "organization_id" => $company->id,
        ]);

        $this->action->execute($admin, $target, UserRole::Student);

        $this->assertNull($target->fresh()->organization_id);
    }

    public function testItIgnoresOrganizationForRoleThatDoesNotNeedOne(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $company = Company::factory()->create();

        $this->action->execute($admin, $target, UserRole::Student, $company->id);

        $this->assertNull($target->fresh()->organization_id);
    }

    public function testItLogsOrganizationChange(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $target = User::factory()->create(["role" => UserRole::Student]);
        $company = Company::factory()->create();

        $this->action->execute($admin, $target, UserRole::CompanyMember, $company->id);

        $activity = Activity::where("subject_id", $target->id)
            ->where("description", "user_role_changed")
            ->latest()
            ->first();

        $this->assertNull($activity->properties["old_organization_id"]);
        $this->assertEquals($company->id, $activity->properties["new_organization_id"]);
    }
}
