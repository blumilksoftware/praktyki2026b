<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Actions\Admin\DeleteOrganizationAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteOrganizationUserAction
{
    public function __construct(
        private readonly TransferOwnership $transferOwnership,
        private readonly DeleteOrganizationAction $deleteOrganizationAction,
        private readonly UserPolicy $userPolicy,
    ) {}

    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            if ($this->userPolicy->isLastOrganizationMember($user)) {
                $organization = match ($user->role) {
                    UserRole::CompanyAdmin => $user->company,
                    UserRole::UniversityAdmin => $user->universityOrganization,
                };
                $this->deleteOrganizationAction->execute($organization);

                return;
            }

            $hasOtherAdmin = User::query()
                ->where("organization_id", $user->organization_id)
                ->where("role", $user->role)
                ->where("status", "!=", UserStatus::Deleted)
                ->whereKeyNot($user->id)
                ->exists();

            if ($hasOtherAdmin) {
                $this->anonymizeUser($user);

                return;
            }

            $memberRole = match ($user->role) {
                UserRole::CompanyAdmin => UserRole::CompanyMember,
                UserRole::UniversityAdmin => UserRole::UniversityMember,
            };

            $newOwner = User::query()
                ->where("organization_id", $user->organization_id)
                ->where("role", $memberRole)
                ->where("status", "!=", UserStatus::Deleted)
                ->whereKeyNot($user->id)
                ->first();

            $this->transferOwnership->execute($user, $newOwner);
            $this->anonymizeUser($user);
        });
    }

    private function anonymizeUser(User $user): void
    {
        $user->forceFill([
            "first_name" => null,
            "last_name" => null,
            "photo_path" => null,
            "pending_email" => null,
            "organization_id" => null,
            "email" => "deleted-" . $user->id . "-" . Str::random(8) . "@deleted.local",
            "status" => UserStatus::Deleted,
        ])->save();
    }
}
