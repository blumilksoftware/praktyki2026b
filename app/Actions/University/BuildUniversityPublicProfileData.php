<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;

class BuildUniversityPublicProfileData
{
    public function execute(University $university): array
    {
        return [
            "id" => $university->id,
            "name" => $university->name,
            "logoUrl" => $university->logo_path,
            "description" => $university->description,
            "email" => $university->email,
            "phone" => $university->phone,
            "website" => $university->website,
            "street" => $university->street,
            "postalCode" => $university->postal_code,
            "city" => $university->city,
            "externalFormUrl" => $university->external_form_url,
            "faculties" => $university->faculties()
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
                ->all(),
        ];
    }
}
