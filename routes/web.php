<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\OfferController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUniversityIsVerified;
use App\Models\Offer;
use Illuminate\Support\Facades\Route;

require __DIR__ . "/frontend.php";

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::patch("/profile", [CompanyController::class, "update"])->name("company.profile.update");
        Route::get("/applications/{application}/cv", [ApplicationController::class, "downloadCv"])->name("company.applications.cv");
    });

Route::middleware(["auth", "can:create," . Offer::class])
    ->prefix("company")
    ->group(function (): void {
        Route::post("/offers", [OfferController::class, "store"])->name("company.offers.store");
    });

Route::middleware(["auth"])
    ->prefix("company")
    ->group(function (): void {
        Route::patch("/offers/{offer}/deactivate", [OfferController::class, "deactivate"])->name("company.offers.deactivate");
        Route::delete("/offers/{offer}", [OfferController::class, "destroy"])->name("company.offers.destroy");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::patch("/profile", [UniversityController::class, "update"])->name("university.profile.update");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::post("/cv", [StudentController::class, "uploadCv"])->name("student.cv.upload");
        Route::delete("/cv", [StudentController::class, "deleteCv"])->name("student.cv.delete");
        Route::post("/offers/{offer}/apply", [StudentController::class, "apply"])->name("student.offers.apply");
        Route::post("/offers/{offer}/favourite", [StudentController::class, "saveOffer"])->name("student.offers.favourite.save");
        Route::delete("/offers/{offer}/favourite", [StudentController::class, "unsaveOffer"])
            ->name("student.offers.favourite.delete")
            ->withTrashed();
        Route::patch("/profile", [StudentController::class, "updateProfile"])->name("student.profile.update");
        Route::post("/profile/photo", [StudentController::class, "uploadPhoto"])->name("student.profile.photo.upload");
        Route::delete("/profile/photo", [StudentController::class, "deletePhoto"])->name("student.profile.photo.delete");
        Route::put("/password", [StudentController::class, "changePassword"])->name("student.password.update");
        Route::patch("/email", [StudentController::class, "changeEmail"])->name("student.email.update");
        Route::delete("/account", [StudentController::class, "deleteAccount"])->name("student.account.delete");
    });

Route::middleware(["auth"])
    ->prefix("onboarding")
    ->group(function (): void {
        Route::post("/dismiss", [OnboardingController::class, "dismiss"])->name("onboarding.dismiss");
    });

Route::middleware(["role:superAdmin"])
    ->prefix("admin")
    ->group(function (): void {
        Route::post("/verify/company/{company}/accept", [AdminController::class, "acceptCompanyVerification"])->name("admin.company.verify.accept");
        Route::post("/verify/company/{company}/reject", [AdminController::class, "rejectCompanyVerification"])->name("admin.company.verify.reject");
        Route::post("/verify/university/{university}/accept", [AdminController::class, "acceptUniversityVerification"])->name("admin.university.verify.accept");
        Route::post("/verify/university/{university}/reject", [AdminController::class, "rejectUniversityVerification"])->name("admin.university.verify.reject");
    });

require __DIR__ . "/auth.php";
