<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])
    ->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])
    ->name("verification.resend");
