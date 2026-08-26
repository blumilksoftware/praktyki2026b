<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Actions\University\BuildFacultiesData;
use App\Actions\University\ResolveUniversityByDomain;
use App\Enums\WorkMode;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;

class BuildStudentProfileData
{
    public function __construct(
        private readonly ResolveUniversityByDomain $resolveUniversityByDomain,
        private readonly BuildFacultiesData $buildFacultiesData,
    ) {}

    public function execute(User $student): array
    {
        $student->loadMissing(["preferredCities", "preferredStudyFields", "studyField.faculty", "universityOrganization"]);

        $preferredCities = $student->preferredCities
            ->pluck("city")
            ->values()
            ->all();

        $studyFieldIds = $student->preferredStudyFields
            ->pluck("id")
            ->values()
            ->all();

        $studyFields = StudyField::query()
            ->select(["id", "name"])
            ->orderBy("name")
            ->get()
            ->map(fn(StudyField $field): array => [
                "value" => $field->id,
                "label" => $field->name,
            ])
            ->all();

        $suggestedUniversity = $student->organization_id === null
            ? $this->resolveUniversityByDomain->execute($student->email)
            : null;

        $mapUniversity = fn(University $university): array => [
            "id" => $university->id,
            "name" => $university->name,
        ];

        $selectedUniversity = $student->universityOrganization ?? $suggestedUniversity;

        return [
            "user" => [
                "first_name" => $student->first_name,
                "last_name" => $student->last_name,
                "email" => $student->email,
                "email_verified_at" => $student->email_verified_at?->toIso8601String(),
                "pending_email" => $student->pending_email,
                "photo_url" => $student->photo_path ? route("student.profile.photo.show") : null,
                "street" => $student->street,
                "postal_code" => $student->postal_code,
                "city" => $student->city,
                "university" => $student->university,
                "university_id" => $selectedUniversity?->id,
                "faculty" => $student->studyField?->faculty?->name,
                "faculty_id" => $student->studyField?->faculty_id,
                "study_field" => $student->studyField?->name,
                "study_field_id" => $student->study_field_id,
                "study_year" => $student->study_year,
                "specialization" => $student->specialization,
                "cv_path" => $student->cv_path,
                "preferred_cities" => $preferredCities,
                "study_field_ids" => $studyFieldIds,
                "skills" => $student->skills ?? [],
                "work_modes" => $student->work_modes ?? [],
            ],
            "studyFields" => $studyFields,
            "faculties" => $selectedUniversity ? $this->buildFacultiesData->execute($selectedUniversity) : [],
            "workModeOptions" => array_column(WorkMode::cases(), "value"),
            "universityOrganization" => $student->universityOrganization ? $mapUniversity($student->universityOrganization) : null,
            "suggestedUniversity" => $suggestedUniversity ? $mapUniversity($suggestedUniversity) : null,
        ];
    }
}
