<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Organization;

use App\Actions\Admin\DeleteOrganizationAction;
use App\Actions\Organization\DeleteOrganizationUserAction;
use App\Actions\Organization\TransferOwnership;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function testCallsDeleteOrganizationActionWhenUserIsLastMember(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $transferOwnership = $this->createMock(TransferOwnership::class);
        $transferOwnership->expects($this->never())->method("execute");

        $deleteOrganizationAction = $this->createMock(DeleteOrganizationAction::class);
        $deleteOrganizationAction->expects($this->once())
            ->method("execute")
            ->with($this->callback(fn(Company $c) => $c->is($company)));

        $action = new DeleteOrganizationUserAction($transferOwnership, $deleteOrganizationAction, new UserPolicy());
        $action->execute($admin);
    }

    public function testCallsTransferOwnershipWhenAnotherMemberExists(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $transferOwnership = $this->createMock(TransferOwnership::class);
        $transferOwnership->expects($this->once())
            ->method("execute")
            ->with($admin, $this->callback(fn(User $u) => $u->is($member)));

        $deleteOrganizationAction = $this->createMock(DeleteOrganizationAction::class);
        $deleteOrganizationAction->expects($this->never())->method("execute");

        $action = new DeleteOrganizationUserAction($transferOwnership, $deleteOrganizationAction, new UserPolicy());
        $action->execute($admin);

        $this->assertSame(UserStatus::Deleted, $admin->fresh()->status);
    }

    public function testIgnoresAlreadyDeletedMembersWhenDecidingToDeleteOrganization(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "status" => UserStatus::Deleted,
        ]);

        $transferOwnership = $this->createMock(TransferOwnership::class);
        $transferOwnership->expects($this->never())->method("execute");

        $deleteOrganizationAction = $this->createMock(DeleteOrganizationAction::class);
        $deleteOrganizationAction->expects($this->once())->method("execute");

        $action = new DeleteOrganizationUserAction($transferOwnership, $deleteOrganizationAction, new UserPolicy());
        $action->execute($admin);
    }
}
