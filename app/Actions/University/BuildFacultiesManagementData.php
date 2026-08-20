<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class BuildFacultiesManagementData
{
    public function execute(University $university): array
    {
        return $university->faculties()
            ->with([
                "studyFields" => fn(Relation $query): Relation => $query
                    ->withCount([
                        "students" => fn(Builder $students): Builder => $students->linkedToUniversity($university),
                        "offers" => fn(Builder $offers): Builder => $offers->published(),
                    ])
                    ->orderBy("name"),
            ])
            ->orderBy("name")
            ->get()
            ->map(fn(Faculty $faculty): array => [
                "id" => $faculty->id,
                "name" => $faculty->name,
                "study_fields" => $faculty->studyFields
                    ->map(fn(StudyField $studyField): array => [
                        "id" => $studyField->id,
                        "name" => $studyField->name,
                        "students_count" => $studyField->students_count,
                        "offers_count" => $studyField->offers_count,
                    ])
                    ->all(),
            ])
            ->all();
    }
}
