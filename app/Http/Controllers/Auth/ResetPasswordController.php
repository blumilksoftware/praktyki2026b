<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\VerifyPasswordResetToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly ResetPassword $resetPassword,
        private readonly VerifyPasswordResetToken $verifyPasswordResetToken,
    ) {}

    public function show(Request $request, string $token): Response
    {
        $email = $request->string("email")->toString();

        return Inertia::render("Auth/ResetPassword", [
            "token" => $token,
            "email" => $email,
            "valid" => $this->verifyPasswordResetToken->execute($email, $token),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $this->resetPassword->execute(
            $request->string("email")->toString(),
            $request->string("password")->toString(),
            $request->string("token")->toString(),
        );

        return redirect()->route("login")->with("status", __("passwords.reset"));
    }
}
