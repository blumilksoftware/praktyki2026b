<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\Company;
use App\Models\University;

class RemovePartnerAction
{
    public function execute(University $university, Company $company): void
    {
        $university->partnerships()->where("company_id", $company->id)->delete();
    }
}
