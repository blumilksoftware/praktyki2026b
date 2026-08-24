<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Admin;

use App\Actions\Admin\DeleteUserAction;
use App\Actions\Organization\DeleteOrganizationUserAction;
use App\Actions\Student\DeleteStudentAccount;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function testItDelegatesStudentsToDeleteStudentAccount(): void
    {
        $student = User::factory()->create(["role" => UserRole::Student]);

        $deleteStudentAccount = $this->createMock(DeleteStudentAccount::class);
        $deleteStudentAccount->expects($this->once())
            ->method("execute")
            ->with($student);

        $deleteOrganizationUser = $this->createMock(DeleteOrganizationUserAction::class);
        $deleteOrganizationUser->expects($this->never())->method("execute");

        $action = new DeleteUserAction($deleteStudentAccount, $deleteOrganizationUser);
        $action->execute($student);
    }

    #[DataProvider("organizationRoleProvider")]
    public function testItDelegatesOrganizationRolesToDeleteOrganizationUserAction(UserRole $role): void
    {
        $user = User::factory()->create(["role" => $role]);

        $deleteStudentAccount = $this->createMock(DeleteStudentAccount::class);
        $deleteStudentAccount->expects($this->never())->method("execute");

        $deleteOrganizationUser = $this->createMock(DeleteOrganizationUserAction::class);
        $deleteOrganizationUser->expects($this->once())
            ->method("execute")
            ->with($user);

        $action = new DeleteUserAction($deleteStudentAccount, $deleteOrganizationUser);
        $action->execute($user);
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function organizationRoleProvider(): array
    {
        return [
            "university member" => [UserRole::UniversityMember],
            "university admin" => [UserRole::UniversityAdmin],
            "company member" => [UserRole::CompanyMember],
            "company admin" => [UserRole::CompanyAdmin],
        ];
    }
}
