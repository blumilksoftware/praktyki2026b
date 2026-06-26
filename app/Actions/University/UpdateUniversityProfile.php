<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\DTO\University\UpdateUniversityProfileData;
use App\Models\University;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;

class UpdateUniversityProfile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(University $university, UpdateUniversityProfileData $data): University
    {
        return DB::transaction(function () use ($university, $data): University {
            $logoPath = $university->logo_path;

            if ($data->logo !== null) {
                $logoPath = $this->fileUploadService->upload($data->logo, "logos", $university->logo_path);
            }

            $domain = $university->domain;

            if ($domain === null || $domain === "") {
                $domain = $data->domain;
            }

            $university->update([
                "domain" => $domain,
                "logo_path" => $logoPath,
                "external_form_url" => $data->externalFormUrl,
            ]);

            if ($data->faculties !== null) {
                $university->faculties()->delete();

                foreach ($data->faculties as $facultyData) {
                    $faculty = $university->faculties()->create([
                        "name" => $facultyData["name"],
                    ]);

                    foreach ($facultyData["study_fields"] as $fieldName) {
                        $faculty->studyFields()->create([
                            "name" => $fieldName,
                        ]);
                    }
                }
            }

            return $university->fresh();
        });
    }
}
