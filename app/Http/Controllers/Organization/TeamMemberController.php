<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Organization\TransferOwnership;
use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly RemoveTeamMember $removeTeamMember,
        private readonly TransferOwnership $transferOwnership,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $role = $user?->role;

        $context = match (true) {
            $role === UserRole::CompanyAdmin || $role === UserRole::CompanyMember => [
                "organization" => $user?->company,
                "typeLabel" => "company",
                "invitationType" => OrganizationType::Company,
                "staffRoles" => [UserRole::CompanyAdmin, UserRole::CompanyMember],
            ],
            $role === UserRole::UniversityAdmin || $role === UserRole::UniversityMember => [
                "organization" => $user?->universityOrganization,
                "typeLabel" => "university",
                "invitationType" => OrganizationType::University,
                "staffRoles" => [UserRole::UniversityAdmin, UserRole::UniversityMember],
            ],
            default => null,
        };

        if ($context === null) {
            abort(403);
        }

        $organization = $context["organization"];

        if (!$organization || $organization->verification_status !== VerificationStatus::Verified) {
            abort(403);
        }

        return inertia("Organization/Team", [
            "organization" => [
                "id" => $organization->id,
                "name" => $organization->name,
                "type" => $context["typeLabel"],
            ],
            "members" => $this->membersFor(
                $organization->users()->whereIn("role", $context["staffRoles"])
            ),
            "invitations" => $this->invitationsFor($organization->id, $context["invitationType"]),
        ]);
    }

    public function destroy(User $member): RedirectResponse
    {
        Gate::authorize("removeFromTeam", $member);

        $this->removeTeamMember->execute($member);

        return back();
    }

    public function transferOwnership(User $member): RedirectResponse
    {
        Gate::authorize("transferOwnershipTo", $member);

        $this->transferOwnership->execute(Auth::user(), $member);

        return back();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function membersFor($users): array
    {
        return $users
            ->select(["id", "first_name", "last_name", "email", "role", "created_at"])
            ->orderBy("created_at")
            ->get()
            ->map(function (User $user): array {
                return [
                    "id" => $user->id,
                    "name" => $user->fullName(),
                    "email" => $user->email,
                    "role" => $user->role->value,
                    "joinedAt" => $user->created_at?->toIso8601String(),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function invitationsFor(string $organizationId, OrganizationType $organizationType): array
    {
        return OrganizationInvitation::query()
            ->where("organization_id", $organizationId)
            ->where("organization_type", $organizationType->value)
            ->where("status", InvitationStatus::Pending->value)
            ->orderBy("created_at")
            ->get()
            ->map(function (OrganizationInvitation $invitation): array {
                return [
                    "id" => $invitation->id,
                    "email" => $invitation->email,
                    "createdAt" => $invitation->created_at->toIso8601String(),
                ];
            })
            ->all();
    }
}
