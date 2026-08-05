<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Support\Collection;

class GetOfferStatusCounts
{
    public function execute(Company $company): Collection
    {
        return $company->offers()
            ->selectRaw("status, count(*) as count")
            ->groupBy("status")
            ->pluck("count", "status");
    }
}
