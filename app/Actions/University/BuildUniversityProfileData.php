<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;

class BuildUniversityProfileData
{
    public function execute(University $university): array
    {
        return [
            "id" => $university->id,
            "name" => $university->name,
            "logoUrl" => $university->logo_path,
            "email" => $university->email,
            "domain" => $university->domain,
            "street" => $university->street,
            "postalCode" => $university->postal_code,
            "city" => $university->city,
            "phone" => $university->phone,
            "website" => $university->website,
            "description" => $university->description,
            "externalFormUrl" => $university->external_form_url,
            "faculties" => $university->faculties()->with("studyFields")->get(),
        ];
    }
}
