<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminIndustryTagController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\OfferController as CompanyOfferController;
use App\Http\Controllers\Company\UniversityController as CompanyUniversityController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\Organization\TeamMemberController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\University\CompanyController as UniversityCompanyController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Controllers\UniversityProfileController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUniversityIsVerified;
use App\Models\Offer;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn() => redirect()->route("login"));

Route::get("/offers", [OfferController::class, "index"])->name("offers.index");
Route::get("/offers/map", [OfferController::class, "map"])->name("offers.map");
Route::get("/offers/{offer}/preview", [OfferController::class, "preview"])
    ->middleware(["auth", "can:update,offer"])
    ->name("offers.preview")
    ->whereUuid("offer");

Route::get("/offers/{offer}", [OfferController::class, "show"])
    ->name("offers.show")
    ->whereUuid("offer");

Route::get("/companies/{company}", [CompanyProfileController::class, "show"])->name("companies.show")->whereUuid("company");
Route::get("/universities/{university}", [UniversityProfileController::class, "show"])->name("universities.show")->whereUuid("university");

Route::get("/dev/components", fn(): Response => inertia("Dev/ComponentShowcase"))
    ->name("dev.components");

Route::middleware(["auth"])
    ->group(function (): void {
        Route::get("/team", [TeamMemberController::class, "index"])->name("team.index");
    });

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
        Route::get("/applications/{application}/cv", [ApplicationController::class, "downloadCv"])->name("company.applications.cv");
        Route::get("/universities", [CompanyUniversityController::class, "index"])->name("company.universities.index");
        Route::get("/students/{student}", [StudentProfileController::class, "show"])->name("company.students.show")->whereUuid("student");
        Route::get("/students/{student}/photo", [StudentProfileController::class, "showPhoto"])->name("company.students.photo")->whereUuid("student");
    });

Route::middleware(["auth", "can:create," . Offer::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/offers", [CompanyOfferController::class, "index"])->name("company.offers");
        Route::get("/offers/create", [CompanyOfferController::class, "create"])->name("company.offers.create");
    });

Route::middleware(["auth"])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/offers/{offer}/edit", [CompanyOfferController::class, "edit"])
            ->middleware("can:update,offer")
            ->name("company.offers.edit");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/dashboard", [UniversityController::class, "index"])->name("university.dashboard");
        Route::get("/profile", [UniversityController::class, "profile"])->name("university.profile");
        Route::get("/companies", [UniversityCompanyController::class, "index"])->name("university.companies.index");
        Route::get("/department", [UniversityController::class, "department"])->name("university.department");
        Route::get("/partnership", [UniversityController::class, "partnership"])->name("university.partnership");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::get("/dashboard", [StudentController::class, "index"])->name("student.dashboard");
        Route::get("/applications", [StudentController::class, "applications"])->name("student.applications");
        Route::get("/profile", [StudentController::class, "profile"])->name("student.profile");
        Route::get("/profile/edit", [StudentController::class, "editProfile"])->name("student.profile.edit");
        Route::get("/settings", [StudentController::class, "settings"])->name("student.settings");
        Route::get("/favourites", [StudentController::class, "favourites"])->name("student.favourites");
    });

Route::middleware(["role:superAdmin"])
    ->prefix("admin")
    ->group(function (): void {
        Route::get("/dashboard", [AdminController::class, "index"])->name("admin.dashboard");
        Route::get("/applications", [AdminController::class, "applications"])->name("admin.applications");
        Route::get("/profile", [AdminController::class, "profile"])->name("admin.profile");
        Route::get("/users", [AdminUserController::class, "index"])->name("admin.users");
        Route::get("/offers", [AdminOfferController::class, "index"])->name("admin.offers");
        Route::get("/industry-tags", [AdminIndustryTagController::class, "index"])->name("admin.industry-tags");
    });
