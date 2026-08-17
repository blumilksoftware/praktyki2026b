<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\University\BuildUniversityPublicProfileData;
use App\Models\University;
use Inertia\Response;

class UniversityProfileController extends Controller
{
    public function __construct(
        private readonly BuildUniversityPublicProfileData $buildUniversityPublicProfileData,
    ) {}

    public function show(string $university): Response
    {
        $verifiedUniversity = University::verified()->findOrFail($university);

        return inertia("University/PublicProfile", [
            "university" => $this->buildUniversityPublicProfileData->execute($verifiedUniversity),
        ]);
    }
}
