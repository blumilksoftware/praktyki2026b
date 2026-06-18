<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::post("/register/student", StudentRegistrationController::class)->name("register.student");
Route::middleware("auth")->get("/user", fn(Request $request): JsonResponse => new JsonResponse($request->user()));

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])
    ->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])
    ->name("verification.resend");
