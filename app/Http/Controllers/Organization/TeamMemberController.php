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
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
                $organization->users()->whereIn("role", $context["staffRoles"]),
                $request,
            ),
            "invitations" => $this->invitationsFor($organization->id, $context["invitationType"], $request),
            "filters" => [
                "member_search" => trim((string)$request->input("member_search", $request->input("search", ""))),
                "invitation_search" => trim((string)$request->input("invitation_search", "")),
                "member_page" => max(1, (int)$request->input("member_page", 1)),
                "invitation_page" => max(1, (int)$request->input("invitation_page", 1)),
                "per_page" => max(1, (int)$request->input("per_page", 10)),
            ],
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
     * @return array<int, array<string, mixed>>|LengthAwarePaginator
     */
    private function membersFor($users, ?Request $request = null)
    {
        $query = $users->select(["id", "first_name", "last_name", "email", "role", "created_at"]);

        $search = trim((string)$request?->input("member_search", $request?->input("search", "")) ?? "");
        $perPage = max(1, (int)($request?->query("per_page", 10) ?? 10));
        $page = max(1, (int)($request?->query("member_page", $request?->query("page", 1) ?? 1) ?? 1));

        if ($search !== "") {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where("first_name", "like", "%{$search}%")
                    ->orWhere("last_name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%")
                    ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%" . strtolower($search) . "%"]);
            });
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "member_page", $page)
            ->appends($request?->only(["member_search", "member_page", "per_page"]) ?? [])
            ->through(fn(User $user): array => [
                "id" => $user->id,
                "name" => $user->fullName(),
                "email" => $user->email,
                "role" => $user->role->value,
                "joinedAt" => $user->created_at?->toIso8601String(),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>|LengthAwarePaginator
     */
    private function invitationsFor(string $organizationId, OrganizationType $organizationType, ?Request $request = null)
    {
        $query = OrganizationInvitation::query()
            ->where("organization_id", $organizationId)
            ->where("organization_type", $organizationType->value)
            ->where("status", InvitationStatus::Pending->value);

        $search = trim((string)$request?->input("invitation_search", "") ?? "");
        $perPage = max(1, (int)($request?->query("per_page", 10) ?? 10));
        $page = max(1, (int)($request?->query("invitation_page", $request?->query("page", 1) ?? 1) ?? 1));

        if ($search !== "") {
            $query->where("email", "like", "%{$search}%");
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "invitation_page", $page)
            ->appends($request?->only(["invitation_search", "invitation_page", "per_page"]) ?? [])
            ->through(fn(OrganizationInvitation $invitation): array => [
                "id" => $invitation->id,
                "email" => $invitation->email,
                "createdAt" => $invitation->created_at->toIso8601String(),
            ]);
    }
}
