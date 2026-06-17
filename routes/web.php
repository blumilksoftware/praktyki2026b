<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CompanyRegistrationController;
use App\Http\Controllers\Company\CompanyDashboardController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::post("/register/company", CompanyRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.company");

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/dashboard", CompanyDashboardController::class)->name("company.dashboard");
    });
