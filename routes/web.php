<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::get("/", fn(): Response => inertia("Welcome"));
Route::get("/admin", fn(): Response => inertia("AdminPanel"));
Route::get("/admin/zgloszenia", fn(): Response => inertia("AdminApplications"));
