<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Organization\TransferOwnership;
use App\DTO\Organization\TeamFiltersData;
use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamFiltersRequest;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Traits\SearchesCaseInsensitively;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class TeamMemberController extends Controller
{
    use SearchesCaseInsensitively;

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
        $filters = TeamFiltersData::fromArray($request->getData());

        return inertia("Organization/Team", [
            "organization" => [
                "id" => $organization->id,
                "name" => $organization->name,
                "type" => $context["typeLabel"],
            ],
            "members" => $this->membersFor(
                $organization->users()->whereIn("role", $context["staffRoles"]),
                $filters,
            ),
            "invitations" => $this->invitationsFor($organization->id, $context["invitationType"], $filters),
            "filters" => [
                "member_search" => $filters->memberSearch,
                "invitation_search" => $filters->invitationSearch,
                "member_page" => $filters->memberPage,
                "invitation_page" => $filters->invitationPage,
                "per_page" => $filters->perPage,
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

    private function membersFor($users, TeamFiltersData $filters): LengthAwarePaginator
    {
        $query = $users->select(["id", "first_name", "last_name", "email", "role", "created_at"]);

        $search = $filters->memberSearch;
        $perPage = $filters->perPage;
        $page = $filters->memberPage;

        if ($search !== "") {
            $this->applyCaseInsensitiveSearch($query->getQuery(), $search, [
                "first_name",
                "last_name",
                "email",
                "COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')",
            ]);
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "member_page", $page)
            ->appends([
                "member_search" => $search,
                "member_page" => $page,
                "per_page" => $perPage,
            ])
            ->through(fn(User $user): array => [
                "id" => $user->id,
                "name" => $user->fullName(),
                "email" => $user->email,
                "role" => $user->role->value,
                "joinedAt" => $user->created_at?->toIso8601String(),
            ]);
    }

    private function invitationsFor(string $organizationId, OrganizationType $organizationType, TeamFiltersData $filters): LengthAwarePaginator
    {
        $query = OrganizationInvitation::query()
            ->where("organization_id", $organizationId)
            ->where("organization_type", $organizationType->value)
            ->where("status", InvitationStatus::Pending->value);

        $search = $filters->invitationSearch;
        $perPage = $filters->perPage;
        $page = $filters->invitationPage;

        if ($search !== "") {
            $this->applyCaseInsensitiveSearch($query, $search, ["email"]);
        }

        $query->orderBy("created_at");

        return $query
            ->paginate($perPage, ["*"], "invitation_page", $page)
            ->appends([
                "invitation_search" => $search,
                "invitation_page" => $page,
                "per_page" => $perPage,
            ])
            ->through(fn(OrganizationInvitation $invitation): array => [
                "id" => $invitation->id,
                "email" => $invitation->email,
                "createdAt" => $invitation->created_at->toIso8601String(),
            ]);
    }
}
