<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\University;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class AddPartnerAction
{
    public function execute(University $university, Company $company): void
    {
        try {
            $university->partnerships()->create([
                "company_id" => $company->id,
                "status" => PartnershipStatus::Active,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                "company" => __("validation.already_partner"),
            ]);
        }
    }
}
