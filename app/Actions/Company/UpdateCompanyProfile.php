<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Company\UpdateCompanyProfileData;
use App\Models\Company;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;

class UpdateCompanyProfile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(Company $company, UpdateCompanyProfileData $data): Company
    {
        $logoPath = $company->logo_path;

        if ($data->logo !== null) {
            if ($logoPath !== null) {
                $oldPath = str_replace("/storage/", "", $logoPath);
                Storage::disk("public")->delete($oldPath);
            }

            $path = $data->logo->store("logos", "public");
            $logoPath = "/storage/" . $path;
        }

        $company->update([
            "logo_path" => $logoPath,
            "description" => $data->description,
            "tags" => $data->tags,
            "website" => $data->website,
            "phone" => $data->phone,
            "street" => $data->street,
            "postal_code" => $data->postal_code,
            "city" => $data->city,
            "nip" => $data->nip,
        ]);

        return $company->fresh();
    }
}
