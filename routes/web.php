<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\OfferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\ProfileRedirectController;
use App\Http\Controllers\SettingsRedirectController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUniversityIsVerified;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

require __DIR__ . "/frontend.php";

Route::post("/language/{locale}", function (string $locale) {
    if (in_array($locale, config("app.available_locales"), true)) {
        Session::put("locale", $locale);
    }

    return redirect()->back();
})->name("language.switch");

Route::get("/profile", [ProfileRedirectController::class, "show"])->name("profile");
Route::get("/profile/edit", [ProfileRedirectController::class, "edit"])->name("profile.edit");
Route::patch("/profile", [ProfileRedirectController::class, "update"])->name("profile.update");
Route::get("/settings", [SettingsRedirectController::class, "show"])->name("settings");

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/profile", [CompanyController::class, "profile"])->name("company.profile");
        Route::patch("/profile", [CompanyController::class, "update"])->name("company.profile.update");
        Route::get("/applications/{application}/cv", [ApplicationController::class, "downloadCv"])->name("company.applications.cv");
        Route::get("/profile/edit", [CompanyController::class, "edit"])->name("company.profile.edit");
        Route::patch("/applications/{application}/status", [ApplicationController::class, "updateStatus"])->name("company.applications.status.update");
    });

Route::middleware(["auth"])
    ->prefix("company")
    ->group(function (): void {
        Route::post("/offers", [OfferController::class, "store"])->name("company.offers.store");
        Route::patch("/offers/{offer}/publish", [OfferController::class, "publish"])->name("company.offers.publish");
        Route::patch("/offers/{offer}/deactivate", [OfferController::class, "deactivate"])->name("company.offers.deactivate");
        Route::delete("/offers/{offer}", [OfferController::class, "destroy"])->name("company.offers.destroy");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/profile", [UniversityController::class, "profile"])->name("university.profile");
        Route::patch("/profile", [UniversityController::class, "update"])->name("university.profile.update");
        Route::get("/profile/edit", [UniversityController::class, "edit"])->name("university.profile.edit");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::get("/dashboard", [StudentController::class, "index"])->name("student.dashboard");

        Route::get("/profile", [StudentController::class, "profile"])->name("student.profile");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::get("/cv", [StudentController::class, "previewCv"])->name("student.cv.preview");
        Route::post("/cv", [StudentController::class, "uploadCv"])->name("student.cv.upload");
        Route::delete("/cv", [StudentController::class, "deleteCv"])->name("student.cv.delete");
        Route::post("/offers/{offer}/apply", [StudentController::class, "apply"])->name("student.offers.apply");
        Route::post("/offers/{offer}/withdraw", [StudentController::class, "withdraw"])->name("student.offers.withdraw");
        Route::post("/offers/{offer}/favourite", [StudentController::class, "saveOffer"])->name("student.offers.favourite.save");
        Route::delete("/offers/{offer}/favourite", [StudentController::class, "unsaveOffer"])->name("student.offers.favourite.delete")->withTrashed();
        Route::get("/favourites", [StudentController::class, "favourites"])->name("student.favourites");
        Route::patch("/profile", [StudentController::class, "updateProfile"])->name("student.profile.update");
        Route::get("/universities/search", [StudentController::class, "searchUniversities"])->name("student.universities.search");
        Route::patch("/university", [StudentController::class, "linkUniversity"])->name("student.university.update");
        Route::post("/profile/photo", [StudentController::class, "uploadPhoto"])->name("student.profile.photo.upload");
        Route::get("/profile/photo", [StudentController::class, "showPhoto"])->name("student.profile.photo.show");
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

Route::middleware(["auth"])
    ->prefix("notifications")
    ->group(function (): void {
        Route::patch("/read-all", [NotificationController::class, "markAllAsRead"])->name("notifications.read-all");
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
