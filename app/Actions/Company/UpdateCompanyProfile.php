<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Company\UpdateCompanyProfileData;
use App\Models\Company;
use App\Services\FileUploadService;

class UpdateCompanyProfile
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(Company $company, UpdateCompanyProfileData $data): Company
    {
        $logoPath = $company->logo_path;

        if ($data->logo !== null) {
            $logoPath = $this->fileUploadService->upload($data->logo, "logos", $company->logo_path);
        }

        $company->update([
            "logo_path" => $logoPath,
            "description" => $data->description,
            "tags" => $data->tags,
        ]);

        return $company->fresh();
    }
}
