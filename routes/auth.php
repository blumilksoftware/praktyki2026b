<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CompanyRegistrationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Auth\UniversityRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::post("/register/company", CompanyRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.company");

Route::post("/register/university", UniversityRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.university");

Route::get("/register/student", fn(): Response => inertia("Auth/RegisterStudent"))->name("register.student.show");
Route::post("/register/student", StudentRegistrationController::class)->name("register.student");

Route::get("/login", [LoginController::class, "show"])->name("login");
Route::post("/login", [LoginController::class, "store"])->name("login.store");

Route::get("/email/verification", fn(Request $request): Response => inertia("Auth/EmailVerificationWaiting", [
    "email" => $request->string("email")->toString(),
]))->name("verification.waiting");

Route::get("/email/verify/{id}/{token}", [EmailVerificationController::class, "verify"])
    ->name("verification.verify");
Route::post("/email/resend", [EmailVerificationController::class, "resend"])
    ->name("verification.resend");
