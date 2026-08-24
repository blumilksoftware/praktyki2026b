<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Actions\Admin\DeleteOrganizationAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteOrganizationUserAction
{
    public function __construct(
        private readonly TransferOwnership $transferOwnership,
        private readonly DeleteOrganizationAction $deleteOrganizationAction,
    ) {}

    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $memberRole = match ($user->role) {
                UserRole::CompanyAdmin => UserRole::CompanyMember,
                UserRole::UniversityAdmin => UserRole::UniversityMember,
                default => null,
            };

            if (!$memberRole) {
                $this->anonymizeUser($user);

                return;
            }

            $newOwner = User::query()
                ->where("organization_id", $user->organization_id)
                ->where("role", $memberRole)
                ->whereKeyNot($user->id)
                ->first();

            if (!$newOwner) {
                $organization = match ($user->role) {
                    UserRole::CompanyAdmin => $user->company,
                    UserRole::UniversityAdmin => $user->universityOrganization,
                };

                $this->deleteOrganizationAction->execute($organization);

                return;
            }

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
            "email" => "deleted-" . $user->id . "-" . Str::random(8) . "@deleted.local",
            "status" => UserStatus::Deleted,
        ])->save();
    }
}
