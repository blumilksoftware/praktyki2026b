<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\University;

class RemovePartnerAction
{
    public function execute(Company $company, University $university): void
    {
        $company->partnerships()->where("university_id", $university->id)->delete();
    }
}
