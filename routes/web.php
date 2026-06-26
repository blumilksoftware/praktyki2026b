<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__ . "/frontend.php";

Route::get("/dev-login", function () {
    $user = User::where("email", "admin@example.com")->first();

    if ($user) {
        Auth::login($user);

        return redirect()->route("admin.dashboard");
    }

    return "Admin user not found";
});

Route::middleware(["auth"])
    ->prefix("admin")
    ->group(function (): void {
        Route::post("/verify/company/{company}/accept", [AdminController::class, "acceptCompanyVerification"])->name("admin.company.verify.accept");
        Route::post("/verify/company/{company}/reject", [AdminController::class, "rejectCompanyVerification"])->name("admin.company.verify.reject");
        Route::post("/verify/university/{university}/accept", [AdminController::class, "acceptUniversityVerification"])->name("admin.university.verify.accept");
        Route::post("/verify/university/{university}/reject", [AdminController::class, "rejectUniversityVerification"])->name("admin.university.verify.reject");
    });

Route::get("/dev/components", fn(): Response => inertia("Dev/ComponentShowcase"))
    ->name("dev.components");

require __DIR__ . "/auth.php";
