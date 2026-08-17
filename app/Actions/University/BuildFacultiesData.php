<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;

class BuildFacultiesData
{
    public function execute(University $university): array
    {
        return $university->faculties()
            ->with("studyFields")
            ->orderBy("name")
            ->get()
            ->map(fn(Faculty $faculty): array => [
                "id" => $faculty->id,
                "name" => $faculty->name,
                "study_fields" => $faculty->studyFields
                    ->sortBy("name")
                    ->map(fn(StudyField $studyField): array => [
                        "id" => $studyField->id,
                        "name" => $studyField->name,
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();
    }
}
