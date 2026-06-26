<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUniversityIsVerified;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/dashboard", [CompanyController::class, "index"])->name("company.dashboard");
        Route::get("/profile", [CompanyController::class, "profile"])->name("company.profile");
    });

    Route::get("/dev/components", fn(): Response => inertia("Dev/ComponentShowcase"))
    ->name("dev.components");

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/dashboard", [UniversityController::class, "index"])->name("university.dashboard");
        Route::get("/profile", [UniversityController::class, "profile"])->name("university.profile");
    });

Route::middleware(["auth"])
    ->prefix("admin")
    ->group(function (): void {
        Route::get("/dashboard", [AdminController::class, "index"])->name("admin.dashboard");
        Route::get("/applications", [AdminController::class, "applications"])->name("admin.applications");
    });
