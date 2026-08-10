<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\University;
use Illuminate\Validation\ValidationException;

class AcceptPartnershipAction
{
    public function execute(Company $company, University $university): void
    {
        $partnership = $company->partnerships()->where("university_id", $university->id)->firstOrFail();

        if ($partnership->status !== PartnershipStatus::Pending) {
            throw ValidationException::withMessages([
                "university" => __("validation.partnership_not_pending"),
            ]);
        }

        if ($partnership->requested_by !== PartnershipInitiator::University) {
            throw ValidationException::withMessages([
                "university" => __("validation.cannot_accept_own_partnership_request"),
            ]);
        }

        $partnership->update(["status" => PartnershipStatus::Active]);
    }
}
