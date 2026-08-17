<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\DTO\University\UpdateUniversityProfileData;
use App\Models\University;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                if ($logoPath !== null) {
                    $oldPath = str_replace("/storage/", "", $logoPath);
                    Storage::disk("public")->delete($oldPath);
                }

                $path = $data->logo->store("logos", "public");

                $logoPath = "/storage/" . $path;
            }

            $domain = $university->domain;

            if ($domain === null || $domain === "") {
                $domain = $data->domain;
            }

            $university->update([
                "domain" => $domain,
                "logo_path" => $logoPath,
                "description" => $data->description,
                "external_form_url" => $data->externalFormUrl,
                "website" => $data->website,
                "phone" => $data->phone,
                "street" => $data->street,
                "postal_code" => $data->postalCode,
                "city" => $data->city,
            ]);

            if ($data->faculties !== null && count($data->faculties) > 0) {
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
