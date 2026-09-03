<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Profile\ResolveProfileBackUrlAction;
use App\Actions\Student\BuildStudentPublicProfileData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class StudentProfileController extends Controller
{
    public function __construct(
        private readonly BuildStudentPublicProfileData $buildStudentPublicProfileData,
        private readonly ResolveProfileBackUrlAction $resolveProfileBackUrlAction,
    ) {}

    public function show(Request $request, User $student): Response
    {
        Gate::authorize("viewProfile", $student);

        $latestApplication = Auth::user()->company->applications()
            ->where("applications.student_id", $student->id)
            ->whereNotNull("applications.cv_path")
            ->latest("applications.created_at")
            ->first();

        return inertia("Student/PublicProfile", [
            "student" => $this->buildStudentPublicProfileData->execute($student),
            "cvUrl" => $latestApplication ? route("company.applications.cv", $latestApplication) : null,
            "backUrl" => $this->resolveProfileBackUrlAction->execute($request),
        ]);
    }
}
