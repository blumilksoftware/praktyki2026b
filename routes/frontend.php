<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUniversityIsVerified;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn() => redirect()->route("login"));

Route::get("/offers", [OfferController::class, "search"])->name("offers.search");

Route::get("/dev/components", fn(): Response => inertia("Dev/ComponentShowcase"))
    ->name("dev.components");

Route::middleware(["auth"])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/verification/pending", [CompanyController::class, "verificationPending"])->name("company.verification.pending");
    });

Route::middleware(["auth"])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/verification/pending", [UniversityController::class, "verificationPending"])->name("university.verification.pending");
    });

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/dashboard", [CompanyController::class, "index"])->name("company.dashboard");
        Route::get("/profile", [CompanyController::class, "profile"])->name("company.profile");
        Route::get("/applications", [ApplicationController::class, "index"])->name("company.applications");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/dashboard", [UniversityController::class, "index"])->name("university.dashboard");
        Route::get("/profile", [UniversityController::class, "profile"])->name("university.profile");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::get("/dashboard", [StudentController::class, "index"])->name("student.dashboard");
        Route::get("/profile", [StudentController::class, "profile"])->name("student.profile");
    });

Route::middleware(["role:superAdmin"])
    ->prefix("admin")
    ->group(function (): void {
        Route::get("/dashboard", [AdminController::class, "index"])->name("admin.dashboard");
        Route::get("/applications", [AdminController::class, "applications"])->name("admin.applications");
    });
