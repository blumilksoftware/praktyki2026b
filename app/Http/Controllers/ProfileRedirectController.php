<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;

class ProfileRedirectController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, [UserRole::CompanyAdmin, UserRole::CompanyMember], true)) {
            return redirect()->route("company.profile");
        }

        if ($user->role === UserRole::Student) {
            return redirect()->route("student.profile");
        }

        if (in_array($user->role, [UserRole::UniversityAdmin, UserRole::UniversityMember], true)) {
            return redirect()->route("university.profile");
        }

        return redirect("/");
    }

    public function edit(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, [UserRole::CompanyAdmin, UserRole::CompanyMember], true)) {
            return redirect()->route("company.profile.edit");
        }

        if ($user->role === UserRole::Student) {
            return redirect()->route("student.profile.edit");
        }

        if (in_array($user->role, [UserRole::UniversityAdmin, UserRole::UniversityMember], true)) {
            return redirect()->route("university.profile.edit");
        }

        return redirect("/");
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, [UserRole::CompanyAdmin, UserRole::CompanyMember], true)) {
            return redirect()->route("company.profile.update", [], 307);
        }

        if ($user->role === UserRole::Student) {
            return redirect()->route("student.profile.update", [], 307);
        }

        if (in_array($user->role, [UserRole::UniversityAdmin, UserRole::UniversityMember], true)) {
            return redirect()->route("university.profile.update", [], 307);
        }

        return abort(403, "Unauthorized");
    }
}
