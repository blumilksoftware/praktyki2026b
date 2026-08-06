<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\InviteTeamMember;
use App\Actions\Company\RevokeInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteTeamMemberRequest;
use App\Models\CompanyInvitation;
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

        $this->inviteTeamMember->execute($user->company, $user, $request->getData()["email"]);

        return back();
    }

    public function destroy(CompanyInvitation $invitation): RedirectResponse
    {
        Gate::authorize("delete", $invitation);

        $this->revokeInvitation->execute($invitation);

        return back();
    }
}
