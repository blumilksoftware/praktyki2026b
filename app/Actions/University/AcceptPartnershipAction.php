<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\University;
use Illuminate\Validation\ValidationException;

class AcceptPartnershipAction
{
    public function execute(University $university, Company $company): void
    {
        $partnership = $university->partnerships()->where("company_id", $company->id)->firstOrFail();

        if ($partnership->status !== PartnershipStatus::Pending) {
            throw ValidationException::withMessages([
                "company" => __("validation.partnership_not_pending"),
            ]);
        }

        if ($partnership->requested_by !== PartnershipInitiator::Company) {
            throw ValidationException::withMessages([
                "company" => __("validation.cannot_accept_own_partnership_request"),
            ]);
        }

        $partnership->update(["status" => PartnershipStatus::Active]);
    }
}
