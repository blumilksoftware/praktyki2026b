<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Admin;

use App\Actions\Admin\ChangeUserRoleAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
