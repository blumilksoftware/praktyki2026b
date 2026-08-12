<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Organization\TransferOwnership;
use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamFiltersRequest;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly RemoveTeamMember $removeTeamMember,
        private readonly TransferOwnership $transferOwnership,
        private readonly UserPolicy $userPolicy,
    ) {}

    public function index(TeamFiltersRequest $request): Response
    {
        $user = $request->user();
        Gate::authorize("viewTeam", $user);

        $context = $this->userPolicy->teamContext($user);
        $organization = $context["organization"];
        $filters = $request->getData();

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
            "filters" => $filters,
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

    private function membersFor($users, TeamFiltersRequest $request): LengthAwarePaginator
    {
        $query = $users->select(["id", "first_name", "last_name", "email", "role", "created_at"]);

        $filters = $request->getData();
        $search = $filters["member_search"];
        $perPage = $filters["per_page"];
        $page = $filters["member_page"];

        if ($search !== "") {
            $searchLower = strtolower($search);

            $query->where(function ($builder) use ($searchLower): void {
                $builder
                    ->whereRaw("LOWER(COALESCE(first_name, '')) ILIKE ?", ["%{$searchLower}%"])
                    ->orWhereRaw("LOWER(COALESCE(last_name, '')) ILIKE ?", ["%{$searchLower}%"])
                    ->orWhereRaw("LOWER(COALESCE(email, '')) ILIKE ?", ["%{$searchLower}%"])
                    ->orWhereRaw("LOWER(COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')) ILIKE ?", ["%{$searchLower}%"]);
            });
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "member_page", $page)
            ->appends([
                "member_search" => $filters["member_search"],
                "member_page" => $filters["member_page"],
                "per_page" => $filters["per_page"],
            ])
            ->through(fn(User $user): array => [
                "id" => $user->id,
                "name" => $user->fullName(),
                "email" => $user->email,
                "role" => $user->role->value,
                "joinedAt" => $user->created_at?->toIso8601String(),
            ]);
    }

    private function invitationsFor(string $organizationId, OrganizationType $organizationType, TeamFiltersRequest $request): LengthAwarePaginator
    {
        $query = OrganizationInvitation::query()
            ->where("organization_id", $organizationId)
            ->where("organization_type", $organizationType->value)
            ->where("status", InvitationStatus::Pending->value);

        $filters = $request->getData();
        $search = $filters["invitation_search"];
        $perPage = $filters["per_page"];
        $page = $filters["invitation_page"];

        if ($search !== "") {
            $query->whereRaw("LOWER(email) ILIKE ?", ["%" . strtolower($search) . "%"]);
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "invitation_page", $page)
            ->appends([
                "invitation_search" => $filters["invitation_search"],
                "invitation_page" => $filters["invitation_page"],
                "per_page" => $filters["per_page"],
            ])
            ->through(fn(OrganizationInvitation $invitation): array => [
                "id" => $invitation->id,
                "email" => $invitation->email,
                "createdAt" => $invitation->created_at->toIso8601String(),
            ]);
    }
}
