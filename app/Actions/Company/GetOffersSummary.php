<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\Offer;
use Illuminate\Support\Collection;

class GetOffersSummary
{
    public function execute(Company $company): Collection
    {
        return $company->offers()
            ->withCount("applications")
            ->orderByDesc("created_at")
            ->orderByDesc("id")
            ->get()
            ->map(fn(Offer $offer): array => [
                "id" => $offer->id,
                "title" => $offer->title,
                "status" => $offer->status->value,
                "spots" => $offer->spots,
                "applications_count" => $offer->applications_count,
            ]);
    }
}
