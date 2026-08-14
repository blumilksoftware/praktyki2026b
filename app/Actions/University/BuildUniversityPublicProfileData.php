<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;

class BuildUniversityPublicProfileData
{
    public function __construct(
        private readonly BuildFacultiesData $buildFacultiesData,
    ) {}

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
            "faculties" => $this->buildFacultiesData->execute($university),
        ];
    }
}
