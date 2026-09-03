<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Profile\ResolveProfileBackUrlAction;
use App\Actions\University\BuildUniversityPublicProfileData;
use App\Enums\UserRole;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class UniversityProfileController extends Controller
{
    public function __construct(
        private readonly BuildUniversityPublicProfileData $buildUniversityPublicProfileData,
        private readonly ResolveProfileBackUrlAction $resolveProfileBackUrl
    ) {}

    public function show(Request $request, string $university): Response
    {
        $universityQuery = Auth::user()?->role === UserRole::SuperAdmin ? University::query() : University::verified();

        $foundUniversity = $universityQuery->findOrFail($university);

        return inertia("University/PublicProfile", [
            "university" => $this->buildUniversityPublicProfileData->execute($foundUniversity),
            "backUrl" => $this->resolveProfileBackUrl->execute($request),
        ]);
    }
}
