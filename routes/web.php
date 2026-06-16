<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));

Route::post("/register/student", StudentRegistrationController::class)->name("register.student");
