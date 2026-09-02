<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\Organization\RemoveTeamMember;
use App\Actions\Account\ChangePassword;
use App\Actions\Account\RequestEmailChange;
use App\Actions\University\BuildUniversityProfileData;
use App\Actions\University\GetStudentsStatistics;
use App\Actions\University\UpdateUniversityProfile;
use App\DTO\University\UpdateUniversityProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateUniversityProfileRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Response;

class UniversityController extends Controller
{
    public function __construct(
        private readonly UpdateUniversityProfile $updateUniversityProfile,
        private readonly GetStudentsStatistics $getStudentsStatistics,
        private readonly BuildUniversityProfileData $buildUniversityProfileData,
        private readonly ChangePassword $changePassword,
        private readonly RequestEmailChange $requestEmailChange,
        private readonly RemoveTeamMember $removeTeamMember,
    ) {}

    public function index(Request $request): Response
    {
        $university = $this->currentUniversity();

        $filters = $request->validate([
            "from" => ["nullable", "date_format:Y-m-d"],
            "to" => ["nullable", "date_format:Y-m-d", "after_or_equal:from"],

            "fieldPage" => ["nullable", "integer", "min:1"],
            "fieldSearch" => ["nullable", "string", "max:255"],
            "fieldSort" => ["nullable", "string", Rule::in(["fieldName", "linkedStudents", "applicationsSubmitted", "acceptedPlacements"])],
            "fieldDirection" => ["nullable", "string", Rule::in(["asc", "desc"])],

            "facultyPage" => ["nullable", "integer", "min:1"],
            "facultySearch" => ["nullable", "string", "max:255"],
            "facultySort" => ["nullable", "string", Rule::in(["facultyName", "linkedStudents", "applicationsSubmitted", "acceptedPlacements"])],
            "facultyDirection" => ["nullable", "string", Rule::in(["asc", "desc"])],
        ]);

        $from = isset($filters["from"]) ? Carbon::parse($filters["from"])->startOfDay() : Carbon::now()->startOfMonth();
        $to = isset($filters["to"]) ? Carbon::parse($filters["to"])->endOfDay() : Carbon::now()->endOfMonth();

        return inertia("University/Dashboard", [
            "data" => $this->getStudentsStatistics->execute(
                university: $university,
                from: $from,
                to: $to,
                fieldPage: (int)($filters["fieldPage"] ?? 1),
                fieldPerPage: 10,
                fieldSearch: $filters["fieldSearch"] ?? null,
                fieldSortBy: $filters["fieldSort"] ?? "fieldName",
                fieldSortDirection: $filters["fieldDirection"] ?? "asc",
                facultyPage: (int)($filters["facultyPage"] ?? 1),
                facultyPerPage: 10,
                facultySearch: $filters["facultySearch"] ?? null,
                facultySortBy: $filters["facultySort"] ?? "facultyName",
                facultySortDirection: $filters["facultyDirection"] ?? "asc",
            ),
            "filters" => [
                ...$filters,
                "from" => $from->toDateString(),
                "to" => $to->toDateString(),
            ],
        ]);
    }

    public function verificationPending(): Response
    {
        return inertia("Auth/VerificationPending", [
            "user" => Auth::user(),
        ]);
    }

    public function profile(): Response
    {
        return inertia("University/Profile/Show", [
            "university" => $this->buildUniversityProfileData->execute($this->currentUniversity()),
            "canEdit" => true,
        ]);
    }

    public function edit(): Response
    {
        return inertia("University/Profile/Edit", [
            "university" => $this->buildUniversityProfileData->execute($this->currentUniversity()),
        ]);
    }

    public function update(UpdateUniversityProfileRequest $request): RedirectResponse
    {
        $data = UpdateUniversityProfileData::fromArray($request->getData());

        $this->updateUniversityProfile->execute($this->currentUniversity(), $data);

        return redirect()->route("university.profile");
    }

    private function currentUniversity(): University
    {
        $university = Auth::user()->universityOrganization;

        if (!$university) {
            abort(404, "University not found for this admin.");
        }

        return $university;
    }

    public function settings(): Response
    {
        $user = Auth::user();

        return inertia("University/Settings", [
            "email" => $user->email,
            "emailVerifiedAt" => $user->email_verified_at?->toIso8601String(),
            "pendingEmail" => $user->pending_email,
        ]);
    }
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->changePassword->execute(Auth::user(), $request->string("password")->toString());

        return back();
    }

    public function changeEmail(ChangeEmailRequest $request): RedirectResponse
    {
        $this->requestEmailChange->execute(Auth::user(), $request->string("email")->toString());

        return back();
    }

    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        $this->removeTeamMember->execute(Auth::user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/");
    }
}
