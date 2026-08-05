<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\userRole;
use Illuminate\Http\Request;

class SettingsRedirectController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role === userRole::Student) {
            return redirect()->route("student.settings");
        }

        if (in_array($user->role, [userRole::CompanyAdmin, userRole::CompanyMember], true)) {
            return redirect()->route("company.profile");
        }

        if ($user->role === userRole::UniversityAdmin) {
            return redirect()->route("university.profile");
        }

        return redirect("/");
    }
}
