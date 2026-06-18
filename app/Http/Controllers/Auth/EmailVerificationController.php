<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $verificationService,
    ) {}

    public function verify(string $id, string $token): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return redirect("/login");
        }

        if (!$this->verificationService->verify($user, $token)) {
            return redirect("/login")->withErrors(["email" => "Invalid or expired verification link."]);
        }

        return redirect("/login");
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            "email" => ["required", "email"],
        ]);

        $user = User::where("email", $request->string("email"))->first();

        if ($user !== null && !$user->hasVerifiedEmail()) {
            $this->verificationService->sendVerificationEmail($user);
        }

        return back();
    }
}
