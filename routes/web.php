<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CompanyRegistrationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Company\CompanyDashboardController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));
Route::get("/admin", fn(): Response => inertia("AdminPanel", [
    "meta" => [
        "title" => "Admin Panel",
    ],
]))->name("admin.panel");
Route::get("/admin/applications", fn(): Response => inertia("AdminApplications", [
    "meta" => [
        "title" => "Admin Applications",
    ],
]))->name("admin.applications");

Route::post("/register/company", CompanyRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.company");

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/dashboard", CompanyDashboardController::class)->name("company.dashboard");
    });
Route::get("/login", [LoginController::class, "show"])->name("login");
Route::post("/login", [LoginController::class, "store"])->name("login.store");

Route::get("/register/student", fn(): Response => inertia("Auth/RegisterStudent"))->name("register.student.show");
Route::post("/register/student", StudentRegistrationController::class)->name("register.student");

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])
    ->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])
    ->name("verification.resend");
