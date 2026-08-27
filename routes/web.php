<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminCityController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminIndustryTagController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DeletionImpactController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\CityGeocodingController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\OfferController;
use App\Http\Controllers\Company\ReviewController as CompanyReviewController;
use App\Http\Controllers\Company\UniversityController as CompanyUniversityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\Organization\TeamInvitationController;
use App\Http\Controllers\Organization\TeamMemberController;
use App\Http\Controllers\ProfileRedirectController;
use App\Http\Controllers\SettingsRedirectController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\University\CompanyController as UniversityCompanyController;
use App\Http\Controllers\University\FacultyController;
use App\Http\Controllers\University\StudyFieldController;
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

Route::get("/geocoding/cities", [CityGeocodingController::class, "suggest"])
    ->name("geocoding.cities")
    ->middleware("throttle:30,1");

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/profile", [CompanyController::class, "profile"])->name("company.profile");
        Route::patch("/profile", [CompanyController::class, "update"])->name("company.profile.update");
        Route::get("/profile/edit", [CompanyController::class, "edit"])->name("company.profile.edit");
        Route::patch("/applications/{application}/status", [ApplicationController::class, "updateStatus"])->name("company.applications.status.update");
        Route::post("/team/invitations", [TeamInvitationController::class, "store"])->middleware("throttle:10,15")->name("company.team.invitations.store");
        Route::delete("/team/invitations/{invitation}", [TeamInvitationController::class, "destroy"])->name("company.team.invitations.destroy");
        Route::delete("/team/members/{member}", [TeamMemberController::class, "destroy"])->name("company.team.members.destroy");
        Route::post("/team/members/{member}/transfer-ownership", [TeamMemberController::class, "transferOwnership"])->name("company.team.members.transfer-ownership");
        Route::post("/universities/{university}/partnership", [CompanyUniversityController::class, "addPartner"])->name("company.universities.partnership.store");
        Route::delete("/universities/{university}/partnership", [CompanyUniversityController::class, "removePartner"])->name("company.universities.partnership.destroy");
        Route::patch("/universities/{university}/partnership/accept", [CompanyUniversityController::class, "acceptPartner"])->name("company.universities.partnership.accept");
        Route::patch("/reviews/{review}/hide", [CompanyReviewController::class, "hide"])->name("company.reviews.hide");
        Route::patch("/reviews/{review}/unhide", [CompanyReviewController::class, "unhide"])->name("company.reviews.unhide");
    });

Route::middleware(["auth"])
    ->prefix("company")
    ->group(function (): void {
        Route::post("/offers", [OfferController::class, "store"])
            ->middleware("throttle:30,1")
            ->name("company.offers.store");

        Route::patch("/offers/{offer}", [OfferController::class, "update"])
            ->middleware("throttle:30,1")
            ->name("company.offers.update");

        Route::patch("/offers/{offer}/publish", [OfferController::class, "publish"])
            ->name("company.offers.publish");

        Route::patch("/offers/{offer}/deactivate", [OfferController::class, "deactivate"])
            ->name("company.offers.deactivate");

        Route::delete("/offers/{offer}", [OfferController::class, "destroy"])
            ->name("company.offers.destroy");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::get("/profile", [UniversityController::class, "profile"])->name("university.profile");
        Route::patch("/profile", [UniversityController::class, "update"])->name("university.profile.update");
        Route::get("/profile/edit", [UniversityController::class, "edit"])->name("university.profile.edit");
        Route::get("/faculties", [FacultyController::class, "index"])->name("university.faculties");
        Route::post("/faculties", [FacultyController::class, "store"])->name("university.faculties.store");
        Route::patch("/faculties/{faculty}", [FacultyController::class, "update"])->name("university.faculties.update");
        Route::delete("/faculties/{faculty}", [FacultyController::class, "destroy"])->name("university.faculties.destroy");
        Route::post("/faculties/{faculty}/study-fields", [StudyFieldController::class, "store"])->name("university.study-fields.store");
        Route::patch("/study-fields/{studyField}", [StudyFieldController::class, "update"])->name("university.study-fields.update");
        Route::delete("/study-fields/{studyField}", [StudyFieldController::class, "destroy"])->name("university.study-fields.destroy");
        Route::post("/companies/{company}/partnership", [UniversityCompanyController::class, "addPartner"])->name("university.companies.partnership.store");
        Route::delete("/companies/{company}/partnership", [UniversityCompanyController::class, "removePartner"])->name("university.companies.partnership.destroy");
        Route::patch("/companies/{company}/partnership/accept", [UniversityCompanyController::class, "acceptPartner"])->name("university.companies.partnership.accept");
        Route::post("/team/invitations", [TeamInvitationController::class, "store"])->middleware("throttle:10,15")->name("university.team.invitations.store");
        Route::delete("/team/invitations/{invitation}", [TeamInvitationController::class, "destroy"])->name("university.team.invitations.destroy");
        Route::delete("/team/members/{member}", [TeamMemberController::class, "destroy"])->name("university.team.members.destroy");
        Route::post("/team/members/{member}/transfer-ownership", [TeamMemberController::class, "transferOwnership"])->name("university.team.members.transfer-ownership");
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
        Route::post("/companies/{company}/reviews", [StudentController::class, "reviewCompany"])->name("student.companies.reviews.store");
        Route::delete("/offers/{offer}/favourite", [StudentController::class, "unsaveOffer"])->name("student.offers.favourite.delete")->withTrashed();
        Route::get("/favourites", [StudentController::class, "favourites"])->name("student.favourites");
        Route::patch("/profile", [StudentController::class, "updateProfile"])->middleware("throttle:20,1")->name("student.profile.update");
        Route::get("/universities/search", [StudentController::class, "searchUniversities"])->name("student.universities.search");
        Route::get("/universities/{university}/faculties", [StudentController::class, "universityFaculties"])->name("student.universities.faculties");
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
        Route::patch("/{notification}/read", [NotificationController::class, "markAsRead"])->name("notifications.read");
    });

Route::middleware(["role:superAdmin"])
    ->prefix("admin")
    ->group(function (): void {
        Route::post("/verify/company/{company}/accept", [AdminController::class, "acceptCompanyVerification"])->name("admin.company.verify.accept");
        Route::post("/verify/company/{company}/reject", [AdminController::class, "rejectCompanyVerification"])->name("admin.company.verify.reject");
        Route::post("/verify/university/{university}/accept", [AdminController::class, "acceptUniversityVerification"])->name("admin.university.verify.accept");
        Route::post("/verify/university/{university}/reject", [AdminController::class, "rejectUniversityVerification"])->name("admin.university.verify.reject");
        Route::patch("/users/{user}/role", [AdminUserController::class, "updateRole"])->name("admin.users.update-role");
        Route::patch("/users/{user}/status", [AdminUserController::class, "updateStatus"])->name("admin.users.update-status");
        Route::get("/users/{user}/deletion-impact", DeletionImpactController::class)->name("admin.users.deletion-impact");
        Route::delete("/users/{user}", [AdminUserController::class, "deleteUser"])->name("admin.users.destroy");
        Route::delete("/companies/{company}", [AdminController::class, "deleteCompany"])->name("admin.companies.destroy");
        Route::delete("/universities/{university}", [AdminController::class, "deleteUniversity"])->name("admin.universities.destroy");
        Route::patch("/offers/{offer}/take-down", [AdminOfferController::class, "takeDown"])->name("admin.offers.take-down");
        Route::delete("/reviews/{review}", [AdminReviewController::class, "destroy"])->name("admin.reviews.destroy");
        Route::post("/cities", [AdminCityController::class, "store"])->name("admin.cities.store");
        Route::patch("/cities/{city}", [AdminCityController::class, "update"])->name("admin.cities.update");
        Route::delete("/cities/{city}", [AdminCityController::class, "destroy"])->name("admin.cities.destroy");
        Route::post("/industry-tags", [AdminIndustryTagController::class, "store"])->name("admin.industry-tags.store");
        Route::patch("/industry-tags/{industryTag}", [AdminIndustryTagController::class, "update"])->name("admin.industry-tags.update");
        Route::delete("/industry-tags/{industryTag}", [AdminIndustryTagController::class, "destroy"])->name("admin.industry-tags.destroy");
    });

require __DIR__ . "/auth.php";
