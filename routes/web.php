<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\UniversityRegistrationController;
use App\Http\Controllers\University\UniversityDashboardController;
use App\Http\Middleware\EnsureUniversityIsVerified;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::get("/login", [LoginController::class, "show"])->name("login");
Route::post("/login", [LoginController::class, "store"])->name("login.store");

Route::get("/register/student", fn(): Response => inertia("Auth/RegisterStudent"))->name("register.student.show");
Route::post("/register/student", StudentRegistrationController::class)->name("register.student");

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])
    ->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])
    ->name("verification.resend");

Route::post("/register/university", UniversityRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.university");

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/dashboard", UniversityDashboardController::class)->name("university.dashboard");
    });
