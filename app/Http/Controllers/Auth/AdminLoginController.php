<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminLoginController extends Controller
{
    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
    ) {}

    public function show(): Response
    {
        return Inertia::render("Auth/AdminLogin");
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $this->authenticateUser->execute(
            $request,
            $request->string("email")->toString(),
            $request->string("password")->toString(),
            $request->boolean("remember"),
        );

        if (auth()->user()->role !== UserRole::SuperAdmin) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(
                [
                    "email" => __("auth.verification.not_admin")],
            );
        }

        return redirect()->intended(route("admin.dashboard"));
    }
}
