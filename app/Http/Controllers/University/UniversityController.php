<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class UniversityController extends Controller
{
    public function index(): Response
    {
        return inertia("University/Dashboard");
    }

    public function profile(): Response
    {
        return inertia("University/Profile", [
            "user" => Auth::user(),
        ]);
    }
}
