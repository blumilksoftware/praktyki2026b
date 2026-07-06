<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\CompanyRegistrationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Auth\UniversityRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::middleware("guest")->group(function (): void {
    Route::get("/register/company", fn(): Response => inertia("Auth/RegisterCompany"))->name("register.company.show");
    Route::post("/register/company", CompanyRegistrationController::class)->middleware("throttle:10,15")->name("register.company");
    Route::post("/register/university", UniversityRegistrationController::class)->middleware("throttle:10,15")->name("register.university");

    Route::get("/register/student", fn(): Response => inertia("Auth/RegisterStudent"))->name("register.student.show");
    Route::post("/register/student", StudentRegistrationController::class)->name("register.student");

    Route::get("/login", [LoginController::class, "show"])->name("login");
    Route::post("/login", [LoginController::class, "store"])->name("login.store");

Route::get("/email/verify/waiting", fn(): Response => inertia("Auth/EmailVerificationWaiting"))->name("verification.waiting");

Route::get("/login", [LoginController::class, "show"])->name("login");
Route::post("/login", [LoginController::class, "store"])->name("login.store");
    Route::get("/forgot-password", [ForgotPasswordController::class, "show"])->name("password.request");
    Route::post("/forgot-password", [ForgotPasswordController::class, "store"])->name("password.email");

    Route::get("/reset-password/{token}", [ResetPasswordController::class, "show"])->name("password.reset");
    Route::post("/reset-password", [ResetPasswordController::class, "store"])->name("password.update");
});

Route::get("/admin/login", [AdminLoginController::class, "show"])->name("admin.login");
Route::post("/admin/login", [AdminLoginController::class, "store"])->name("admin.login.store");

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])->name("verification.resend");

Route::get("/auth/google/redirect", [GoogleOAuthController::class, "redirect"])->name("auth.google.redirect");
Route::get("/auth/google/callback", [GoogleOAuthController::class, "callback"])->name("auth.google.callback");
