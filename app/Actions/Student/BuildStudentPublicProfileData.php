<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\VerificationStatus;
use App\Models\User;

class BuildStudentPublicProfileData
{
    public function execute(User $student): array
    {
        $student->loadMissing(["preferredCities", "preferredStudyFields", "studyField.faculty", "universityOrganization"]);

        $universityOrganization = $student->universityOrganization;
        $isUniversityVerified = $universityOrganization?->verification_status === VerificationStatus::Verified;

        return [
            "id" => $student->id,
            "first_name" => $student->first_name,
            "last_name" => $student->last_name,
            "full_name" => $student->fullName(),
            "email" => $student->email,
            "age" => $student->age,
            "city" => $student->city,
            "university" => $student->universityOrganization->name ?? $student->university,
            "university_id" => $isUniversityVerified ? $universityOrganization->id : null,
            "faculty" => $student->studyField?->faculty?->name,
            "study_field" => $student->studyField?->name,
            "study_year" => $student->study_year,
            "specialization" => $student->specialization,
            "skills" => $student->skills ?? [],
            "work_modes" => $student->work_modes ?? [],
            "preferred_cities" => $student->preferredCities->pluck("city")->values()->all(),
            "preferred_study_fields" => $student->preferredStudyFields
                ->sortBy("name")
                ->pluck("name")
                ->values()
                ->all(),
        ];
    }
}
