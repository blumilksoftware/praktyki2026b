<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
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
        $this->authenticateUser->execute(
            $request,
            $request->string("email")->toString(),
            $request->string("password")->toString(),
            $request->boolean("remember"),
        );

        return redirect()->intended("/home");
    }
}
