<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
    ) {}

    public function show(): Response
    {
        return Inertia::render("Auth/Login");
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $this->authenticateUser->execute(
            $request,
            $request->string("email")->toString(),
            $request->string("password")->toString(),
            $request->boolean("remember"),
        );

        if ($user === null) {
            throw ValidationException::withMessages([
                "email" => __("auth.failed"),
            ]);
        }

        if ($user && !$user->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->flash("requires_verification", true);

            throw ValidationException::withMessages([
                "email" => __("auth.verification.not_verified"),
            ]);
        }

        if ($user->role === UserRole::SuperAdmin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                "email" => __("auth.failed"),
            ]);
        }

        if (!$user->hasVerifiedEmail() || $user->status === UserStatus::Pending) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            app(EmailVerificationService::class)->sendVerificationEmail($user);

            return redirect()->route("verification.waiting")
                ->with("email", $user->email)
                ->with("requires_verification", true);
        }

        $redirectUrl = match ($user->role) {
            UserRole::CompanyAdmin, UserRole::CompanyMember => route("company.dashboard"),
            UserRole::UniversityAdmin, UserRole::UniversityMember => route("university.dashboard"),
            UserRole::Student => route("student.dashboard"),
            default => "/",
        };

        return redirect()->intended($redirectUrl);
    }
}
