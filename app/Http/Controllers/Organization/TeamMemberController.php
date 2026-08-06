<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Organization\TransferOwnership;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly RemoveTeamMember $removeTeamMember,
        private readonly TransferOwnership $transferOwnership,
    ) {}

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
}
