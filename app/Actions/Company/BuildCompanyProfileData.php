<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\PartnershipStatus;
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
                ->withCount("acceptedApplications")
                ->latest()
                ->get()
                ->map(fn($offer) => [
                    "id" => $offer->id,
                    "title" => $offer->title,
                    "description" => $offer->description,
                    "spots" => $offer->spots,
                    "remaining_spots" => $offer->remainingSpots(),
                ]),
            "verification_status" => $company->verification_status,
            "partners" => $company->partnerships()
                ->where("status", PartnershipStatus::Active)
                ->with("university:id,name,city")
                ->get()
                ->map(fn($partnership) => [
                    "id" => $partnership->university->id,
                    "name" => $partnership->university->name,
                    "city" => $partnership->university->city,
                ]),
        ];
    }
}
