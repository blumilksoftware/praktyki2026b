<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;

class SettingsRedirectController extends Controller
{
public function show(Request $request)
{
$user = $request->user();

if ($user->role === UserRole::SuperAdmin) {
return redirect()->route("admin.settings");
}

if ($user->role === UserRole::Student) {
return redirect()->route("student.settings");
}

if (in_array($user->role, [UserRole::CompanyAdmin, UserRole::CompanyMember], true)) {
return redirect()->route("company.settings");
}

if (in_array($user->role, [UserRole::UniversityAdmin, UserRole::UniversityMember], true)) {
return redirect()->route("university.settings");
}

return redirect("/");
}
}
