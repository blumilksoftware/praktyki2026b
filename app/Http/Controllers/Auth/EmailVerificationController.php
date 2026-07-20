<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Log;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $verificationService,
    ) {}

    public function verify(string $id, string $token): Response
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return inertia("Auth/EmailVerificationResult", ["status" => "already_verified"]);
        }

        if (!$this->verificationService->verify($user, $token)) {
            return inertia("Auth/EmailVerificationResult", ["status" => "invalid"]);
        }

        return inertia("Auth/EmailVerificationResult", ["status" => "success"]);
    }

    public function verifyChange(string $id, string $token): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!$this->verificationService->confirmEmailChange($user, $token)) {
            return redirect("/login")->withErrors(["email" => __("auth.verification.invalid_link")]);
        }

        return redirect("/login");
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            "email" => ["required", "email"],
        ]);

        $user = User::where("email", $request->string("email"))->first();

        Log::info("Resend attempt", [
            "email" => $request->input("email"),
            "user_found" => $user !== null,
            "already_verified" => $user?->hasVerifiedEmail(),
        ]);

        if ($user !== null && !$user->hasVerifiedEmail()) {
            $this->verificationService->sendVerificationEmail($user);
            Log::info("Verification email queued for user " . $user->id);
        }

        return back()
            ->with("status", "verification-resend")
            ->with("requires_verification", true)
            ->with("email", $request->string("email"));
    }
}
