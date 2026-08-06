<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\InviteTeamMember;
use App\Actions\Organization\RevokeInvitation;
use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteTeamMemberRequest;
use App\Models\Company;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TeamInvitationController extends Controller
{
    public function __construct(
        private readonly InviteTeamMember $inviteTeamMember,
        private readonly RevokeInvitation $revokeInvitation,
    ) {}

    public function store(InviteTeamMemberRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->inviteTeamMember->execute($this->organizationFor($user), $user, $request->getData()["email"]);

        return back();
    }

    public function destroy(OrganizationInvitation $invitation): RedirectResponse
    {
        Gate::authorize("delete", $invitation);

        $this->revokeInvitation->execute($invitation);

        return back();
    }

    private function organizationFor(User $user): Company|University
    {
        return $user->role->organizationType() === OrganizationType::University
            ? $user->universityOrganization
            : $user->company;
    }
}
