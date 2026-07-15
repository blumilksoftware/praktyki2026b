<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\StudyField;
use App\Models\User;

class BuildStudentProfileData
{
    public function execute(User $student): array
    {
        $student->loadMissing(["preferredCities", "preferredStudyFields"]);

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

        return [
            "user" => [
                "first_name" => $student->first_name,
                "last_name" => $student->last_name,
                "email" => $student->email,
                "email_verified_at" => $student->email_verified_at?->toIso8601String(),
                "pending_email" => $student->pending_email,
                "photo_url" => $student->photo_path ? route("student.profile.photo.show") : null,
                "age" => $student->age,
                "location" => $student->location,
                "university" => $student->university,
                "study_field" => $student->study_field,
                "study_year" => $student->study_year,
                "specialization" => $student->specialization,
                "cv_path" => $student->cv_path,
                "preferred_cities" => $preferredCities,
                "study_field_ids" => $studyFieldIds,
            ],
            "study_fields" => $studyFields,
        ];
    }
}
