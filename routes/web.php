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
use App\Models\User;
use Illuminate\Http\Request;
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

Route::get("/profile", function (Request $request) {
    $user = $request->user();

    if ($user->role->value === "companyAdmin") {
        return redirect()->route("company.profile");
    }

    if ($user->role->value === "student") {
        return redirect()->route("student.profile");
    }

    if ($user->role->value === "universityAdmin ") {
        return redirect()->route("university.profile");
    }

    return redirect("/");
})->name("profile");

Route::get("/profile/edit", function (Request $request) {
    $user = $request->user();

    if ($user->role->value === "companyAdmin") {
        return redirect()->route("company.profile.edit");
    }

    if ($user->role->value === "student") {
        return redirect()->route("student.profile.edit");
    }

    return redirect("/");
})->name("profile.edit");

Route::patch("/profile", function (Request $request) {
    $user = $request->user();

    if ($user->role->value === "companyAdmin") {
        return redirect()->route("company.profile.update", [], 307);
    }

    if ($user->role->value === "student") {
        return redirect()->route("student.profile.update", [], 307);
    }

    if ($user->role->value === "universityAdmin") {
        return redirect()->route("university.profile.update", [], 307);
    }

    return abort(403, "Unauthorized");
})->name("profile.update");

Route::middleware(["auth", EnsureCompanyIsVerified::class])
    ->prefix("company")
    ->group(function (): void {
        Route::get("/profile", [CompanyController::class, "profile"])->name("company.profile");
        Route::patch("/profile", [CompanyController::class, "update"])->name("company.profile.update");
        Route::get("/applications/{application}/cv", [ApplicationController::class, "downloadCv"])->name("company.applications.cv");
        Route::get("/profile/edit", [CompanyController::class, "edit"])->name("company.profile.edit");
    });

Route::middleware(["auth", "can:create," . Offer::class])
    ->prefix("company")
    ->group(function (): void {
        Route::post("/offers", [OfferController::class, "store"])->name("company.offers.store");
    });

Route::middleware(["auth", "can:create," . Offer::class])
    ->prefix("company")
    ->group(function (): void {
        Route::post("/offers", [OfferController::class, "store"])->name("company.offers.store");
    });

Route::middleware(["auth", EnsureUniversityIsVerified::class])
    ->prefix("university")
    ->group(function (): void {
        Route::patch("/profile", [UniversityController::class, "update"])->name("university.profile.update");
        Route::get("/profile/edit", [UniversityController::class, "edit"])->name("university.profile.edit");
    });

Route::middleware(["auth", "can:access-student-panel"])
    ->prefix("student")
    ->group(function (): void {
        Route::post("/cv", [StudentController::class, "uploadCv"])->name("student.cv.upload");
        Route::delete("/cv", [StudentController::class, "deleteCv"])->name("student.cv.delete");
        Route::post("/offers/{offer}/apply", [StudentController::class, "apply"])->name("student.offers.apply");
        Route::get("/profile/edit", [StudentController::class, "edit"])->name("student.profile.edit");
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
