<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AcceptInvitation;
use App\Enums\InvitationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Models\OrganizationInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvitationAcceptController extends Controller
{
    public function __construct(
        private readonly AcceptInvitation $acceptInvitation,
    ) {}

    public function show(string $token): Response
    {
        $invitation = OrganizationInvitation::query()
            ->where("token", hash("sha256", $token))
            ->first();

        if (
            $invitation !== null
            && (
                $invitation->status !== InvitationStatus::Pending
                || $invitation->isExpired()
            )
        ) {
            abort(404);
        }

        return Inertia::render("Auth/AcceptInvitation", ["token" => $token]);
    }

    public function store(AcceptInvitationRequest $request, string $token): RedirectResponse
    {
        $data = $request->getData();

        $user = $this->acceptInvitation->execute(
            $token,
            $data["first_name"],
            $data["last_name"],
            $data["password"],
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->role === UserRole::UniversityMember ? "university.dashboard" : "company.dashboard");
    }
}
