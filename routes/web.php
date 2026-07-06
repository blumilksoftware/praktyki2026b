<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\University\UniversityController;
use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\EnsureUniversityIsVerified;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

require __DIR__ . "/frontend.php";

Route::post("/language/{locale}", function (string $locale) {
    if (in_array($locale, ["pl", "en"], true)) {
        Session::put("locale", $locale);
    }

    return redirect()->back();
})->name("language.switch");

Route::get("/dev-login", function () {
    $user = User::where("email", "admin@example.com")->first();

    if ($user) {
        if (method_exists($user, "markEmailAsVerified") && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->save();
        }
        Auth::login($user);
        session()->regenerate();

        return redirect()->route("admin.dashboard");
    }

    return "Admin user not found";
});

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::patch("/profile", [CompanyController::class, "update"])->name("company.profile.update");
        Route::get("/applications/{application}/cv", [ApplicationController::class, "downloadCv"])->name("company.applications.cv");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::patch("/profile", [UniversityController::class, "update"])->name("university.profile.update");
    });

Route::middleware(["auth", EnsureStudent::class])
    ->prefix("student")
    ->group(function (): void {
        Route::post("/cv", [StudentController::class, "uploadCv"])->name("student.cv.upload");
        Route::delete("/cv", [StudentController::class, "deleteCv"])->name("student.cv.delete");
        Route::post("/offers/{offer}/apply", [StudentController::class, "apply"])->name("student.offers.apply");
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
