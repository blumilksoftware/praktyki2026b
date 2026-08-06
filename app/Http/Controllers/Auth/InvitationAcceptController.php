<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AcceptInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
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

        return redirect()->route("company.dashboard");
    }
}
