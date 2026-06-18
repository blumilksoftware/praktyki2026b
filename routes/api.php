<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\CompanyRegistrationController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/register/student", StudentRegistrationController::class)->name("register.student");

Route::post("/register/company", CompanyRegistrationController::class)
    ->middleware("throttle:10,15")
    ->name("register.company");

Route::middleware("auth:sanctum")->get("/user", fn(Request $request): JsonResponse => new JsonResponse($request->user()));
