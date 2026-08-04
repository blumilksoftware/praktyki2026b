<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;

class BuildCompanyProfileData
{
    public function execute(Company $company): array
    {
        return [
            "id" => $company->id,
            "name" => $company->name,
            "logoUrl" => $company->logo_path,
            "tags" => $company->tags ?? [],
            "description" => $company->description,
            "email" => $company->email,
            "phone" => $company->phone,
            "website" => $company->website,
            "street" => $company->street,
            "postalCode" => $company->postal_code,
            "city" => $company->city,
            "nip" => $company->nip,
            "offers" => $company->offers()
                ->published()
                ->select("id", "title", "description", "spots")
                ->latest()
                ->get(),
            "verification_status" => $company->verification_status,
        ];
    }
}
