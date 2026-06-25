<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly SendPasswordResetLink $sendPasswordResetLink,
    ) {}

    public function show(): Response
    {
        return Inertia::render("Auth/ForgotPassword");
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $this->sendPasswordResetLink->execute($request->string("email")->toString());

        return back()->with("status", __("passwords.sent"));
    }
}
