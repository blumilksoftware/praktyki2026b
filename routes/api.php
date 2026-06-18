<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get("/health", fn() => response()->json(["status" => "ok"]))->name("health");
