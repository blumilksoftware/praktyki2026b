<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render("Auth/Login");
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (!Auth::attempt($request->only("email", "password"), $request->boolean("remember"))) {
            throw ValidationException::withMessages([
                "email" => trans("auth.failed"),
            ]);
        }

        $user = Auth::user();

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
                "email" => __("auth.verification.admin_restricted"),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended("/");
    }
}
